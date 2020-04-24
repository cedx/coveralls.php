<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isInstanceOf, logicalNot};

/** @testdox Coveralls\Parsers\Lcov */
class LcovTest extends TestCase {

  /** @testdox ::parseReport() */
  function testParseReport(): void {
    $report = new \SplFileObject('test/fixtures/lcov.info');

    // It should properly parse LCOV reports.
    $job = Lcov::parseReport((string) $report->fread($report->getSize()));
    $files = $job->getSourceFiles();
    [$firstFile, $secondFile, $thirdFile] = $files;
    assertThat($files, countOf(3));

    /** @var SourceFile $firstFile */
    $subset = [null, 2, 2, 2, 2, null];
    assertThat($firstFile, isInstanceOf(SourceFile::class));
    assertThat($firstFile->getBranches(), isEmpty());
    assertThat(array_intersect($subset, (array) $firstFile->getCoverage()), equalTo($subset));
    assertThat($firstFile->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/Client.php')));
    assertThat($firstFile->getSourceDigest(), logicalNot(isEmpty()));

    /** @var SourceFile $secondFile */
    $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
    assertThat((array) $secondFile->getBranches(), equalTo([8, 0, 0, 2, 8, 0, 1, 2, 11, 0, 0, 2, 11, 0, 1, 2]));
    assertThat(array_intersect($subset, (array) $secondFile->getCoverage()), equalTo($subset));
    assertThat($secondFile->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/Configuration.php')));
    assertThat($secondFile->getSourceDigest(), logicalNot(isEmpty()));

    /** @var SourceFile $thirdFile */
    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    assertThat((array) $thirdFile->getBranches(), equalTo([8, 0, 0, 2, 8, 0, 1, 0, 11, 0, 0, 0, 11, 0, 1, 2]));
    assertThat(array_intersect($subset, (array) $thirdFile->getCoverage()), equalTo($subset));
    assertThat($thirdFile->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/GitCommit.php')));
    assertThat($thirdFile->getSourceDigest(), logicalNot(isEmpty()));

    // It should throw an exception when parsing reports with invalid source file.
    $this->expectException(\RuntimeException::class);
    $report = new \SplFileObject('test/fixtures/invalid_lcov.info');
    Lcov::parseReport((string) $report->fread($report->getSize()));
  }
}
