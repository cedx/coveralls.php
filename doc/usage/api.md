path: blob/master
source: lib/Client.php

# Application programming interface
The hard way. Use the `Coveralls\Client` class to upload your coverage reports:

```php
<?php
use Coveralls\{Client, ClientException};

function main(): void {
  try {
    $coverage = @file_get_contents('/path/to/coverage.report');
    (new Client)->upload($coverage);
    echo 'The report was sent successfully.';
  }

  catch (Throwable $e) {
    echo 'An error occurred: ', $e->getMessage();
    if ($e instanceof ClientException) echo 'From: ', $e->getUri(), PHP_EOL;
  }
}
```

The `Client->upload()` method throws an [`InvalidArgumentException`](https://www.php.net/manual/en/class.invalidargumentexception.php)
if the input report is invalid. It throws a `Coveralls\ClientException` if any error occurred while uploading the report.

## Client events
The `Coveralls\Client` class is a [`League\Event\Emitter`](https://event.thephpleague.com/2.0/emitter/basic-usage) that triggers some events during its life cycle.

### The `Client::eventRequest` event
Emitted every time a request is made to the remote service:

```php
<?php
use Coveralls\{Client, RequestEvent};

function main(): void {
  $client = new Client;
  $client->addListener(Client::eventRequest, function(RequestEvent $event) {
    echo 'Client request: ', $event->getRequest()->getUri();
  });
}
```

### The `Client::eventResponse` event
Emitted every time a response is received from the remote service:

```php
<?php
use Coveralls\{Client, ResponseEvent};

function main(): void {
  $client = new Client;
  $client->addListener(Client::eventResponse, function(ResponseEvent $event) {
    echo 'Server response: ', $event->getResponse()->getStatusCode();
  });
}
```
