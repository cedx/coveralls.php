<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{Job, SourceFile};
use Lcov\{Record, Report};
use Webmozart\PathUtil\{Path};

/** Parses [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage reports. */
abstract class Lcov {

  /**
   * Parses the specified coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Job The job corresponding to the specified coverage report.
   * @throws \RuntimeException A source file is not found or empty.
   */
  static function parseReport(string $report): Job {
    $records = Report::fromCoverage($report)->getRecords()->getArrayCopy();
    $workingDir = (string) getcwd();

    return new Job(array_map(function(Record $record) use ($workingDir) {
      $sourceFile = $record->getSourceFile();
      $source = (string) @file_get_contents($sourceFile);
      if (!mb_strlen($source)) throw new \RuntimeException("Source file not found or empty: $sourceFile");

      $lineCoverage = new \SplFixedArray(count(preg_split('/\r?\n/', $source) ?: []));
      if ($lines = $record->getLines()) foreach ($lines->getData() as $lineData) {
        /** @var \Lcov\LineData $lineData */
        $lineCoverage[$lineData->getLineNumber() - 1] = $lineData->getExecutionCount();
      }

      $branchCoverage = [];
      if ($branches = $record->getBranches()) foreach ($branches->getData() as $branchData) {
        /** @var \Lcov\BranchData $branchData */
        array_push(
          $branchCoverage,
          $branchData->getLineNumber(),
          $branchData->getBlockNumber(),
          $branchData->getBranchNumber(),
          $branchData->getTaken()
        );
      }

      $filename = Path::isAbsolute($sourceFile) ? Path::makeRelative($sourceFile, $workingDir) : Path::canonicalize($sourceFile);
      return new SourceFile(str_replace('/', DIRECTORY_SEPARATOR, $filename), md5($source), $source, $lineCoverage->toArray(), $branchCoverage);
    }, $records));
  }
}
