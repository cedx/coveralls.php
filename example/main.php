<?php declare(strict_types=1);
use Coveralls\{Client, ClientException};

/** Uploads a coverage report. */
function main(): void {
  try {
    $coverage = file_get_contents('/path/to/coverage.report');
    (new Client)->upload($coverage);
    echo 'The report was sent successfully.';
  }

  catch (Throwable $e) {
    echo 'An error occurred: ', $e->getMessage();
    if ($e instanceof ClientException) echo 'From: ', $e->getUri(), PHP_EOL;
  }
}
