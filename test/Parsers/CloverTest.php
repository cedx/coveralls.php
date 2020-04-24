<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isInstanceOf, logicalNot};

/** @testdox Coveralls\Parsers\Clover */
class CloverTest extends TestCase {

  /** @testdox ::parseReport() */
  function testParseReport(): void {
    $report = new \SplFileObject('test/fixtures/clover.xml');

    // It should properly parse Clover reports.
    $job = Clover::parseReport((string) $report->fread($report->getSize()));
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
    assertThat($secondFile, isInstanceOf(SourceFile::class));
    assertThat($secondFile->getBranches(), isEmpty());
    assertThat(array_intersect($subset, (array) $secondFile->getCoverage()), equalTo($subset));
    assertThat($secondFile->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/Configuration.php')));
    assertThat($secondFile->getSourceDigest(), logicalNot(isEmpty()));

    /** @var SourceFile $thirdFile */
    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    assertThat($thirdFile, isInstanceOf(SourceFile::class));
    assertThat($thirdFile->getBranches(), isEmpty());
    assertThat(array_intersect($subset, (array) $thirdFile->getCoverage()), equalTo($subset));
    assertThat($thirdFile->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/GitCommit.php')));
    assertThat($thirdFile->getSourceDigest(), logicalNot(isEmpty()));

    // It should throw an exception if the Clover report is invalid or empty.
    $this->expectException(\InvalidArgumentException::class);
    Clover::parseReport('<coverage><foo/></coverage>');
  }
}
