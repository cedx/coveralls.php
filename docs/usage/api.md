# Application programming interface
The hard way. Use the `Coveralls\Client` class to upload your coverage reports:

```php
use Coveralls\{Client, ClientException};

function main(): void {
  try {
    $coverage = file_get_contents("/path/to/coverage.report");
    (new Client)->upload($coverage);
    print "The report was sent successfully.";
  }

  catch (Throwable $e) {
    print "An error occurred: {$e->getMessage()}" . PHP_EOL;
    if ($e instanceof ClientException) print "From: {$e->getUri()}" . PHP_EOL;
  }
}
```

The `Client->upload()` method throws an `InvalidArgumentException` if the input report is invalid.
It throws a `Coveralls\ClientException` if any error occurred while uploading the report.

## Client events
The `Coveralls\Client` class is an [EventDispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) that triggers some events during its life cycle.

### The "request" event
Emitted every time a request is made to the remote service:

```php
use Coveralls\{Client, RequestEvent};

function main(): void {
  $client = new Client;
  $client->addListener(Client::eventRequest, fn(RequestEvent $event) =>
    print "Client request: {$event->getRequest()->getUri()}"
  );
}
```

### The "response" event
Emitted every time a response is received from the remote service:

```php
use Coveralls\{Client, ResponseEvent};

function main(): void {
  $client = new Client;
  $client->addListener(Client::eventResponse, fn(ResponseEvent $event) =>
    print "Server response: {$event->getResponse()->getStatusCode()}"
  );
}
```
