<?php declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\Client */
class ClientTest extends TestCase {

  /** @testdox ->upload() */
  function testUpload(): void {
    // It should throw an exception with an invalid coverage report.
    $this->expectException(\InvalidArgumentException::class);
    (new Client)->upload('end_of_record');
  }

  /** @testdox ->uploadJob() */
  function testUploadJob(): void {
    // It should throw an exception with an empty coverage job.
    $this->expectException(\InvalidArgumentException::class);
    (new Client)->uploadJob(new Job);
  }
}
