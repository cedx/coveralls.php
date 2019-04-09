<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\Parsers\Lcov` class.
 */
class LcovTest extends TestCase {

  /**
   * Tests the `Lcov::parseReport()` method.
   * @test
   */
  function testParseReport(): void {
    // It should properly parse LCOV reports.
    $job = Lcov::parseReport((string) file_get_contents('test/fixtures/lcov.info'));
    $files = $job->getSourceFiles();
    assertThat($files, countOf(3));

    $subset = [null, 2, 2, 2, 2, null];
    assertThat($files[0], isInstanceOf(SourceFile::class));
    assertThat($files[0]->getName(), equalTo('src/Http/Client.php'));
    assertThat($files[0]->getSourceDigest(), logicalNot(isEmpty()));
    assertThat(array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()), equalTo($subset));

    $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
    assertThat($files[1]->getName(), equalTo('src/Configuration.php'));
    assertThat($files[1]->getSourceDigest(), logicalNot(isEmpty()));
    assertThat(array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()), equalTo($subset));

    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    assertThat($files[2]->getName(), equalTo('src/GitCommit.php'));
    assertThat($files[2]->getSourceDigest(), logicalNot(isEmpty()));
    assertThat(array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()), equalTo($subset));

    // It should throw RuntimeException when parsing LCOV reports with invalid source file.
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Source file not found: src/Http/NotFound.php');
    Lcov::parseReport((string) file_get_contents('test/fixtures/invalid_lcov.info'));
  }
}
