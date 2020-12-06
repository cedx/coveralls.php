<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{Job, SourceFile};
use lcov\{Record, Report};
use Webmozart\PathUtil\Path;

/** Parses [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage reports. */
abstract class Lcov {

	/**
	 * Parses the specified coverage report.
	 * @param string $report A coverage report in LCOV format.
	 * @return Job The job corresponding to the specified coverage report.
	 * @throws \RuntimeException A source file is not found or empty.
	 */
	static function parseReport(string $report): Job {
		$workingDir = (string) getcwd();
		$sourceFiles = Report::fromCoverage($report)->records->map(function(Record $record) use ($workingDir) {
			$sourceFile = new \SplFileObject($record->sourceFile);
			$sourceFile->isReadable() || throw new \RuntimeException("Source file not found: {$sourceFile->getPathname()}");

			$source = (string) $sourceFile->fread($sourceFile->getSize());
			mb_strlen($source) || throw new \RuntimeException("Source file empty: {$sourceFile->getPathname()}");

			/** @var \lcov\LineCoverage|null $lines */
			$lines = $record->lines;
			$lineCoverage = new \SplFixedArray(count(preg_split('/\r?\n/', $source) ?: []));
			if ($lines) foreach ($lines->data as $lineData) {
				/** @var \lcov\LineData $lineData */
				$lineCoverage[$lineData->lineNumber - 1] = $lineData->executionCount;
			}

			/** @var \lcov\BranchCoverage|null $branches */
			$branches = $record->branches;
			$branchCoverage = [];
			if ($branches) foreach ($branches->data as $branchData) {
				/** @var \lcov\BranchData $branchData */
				array_push($branchCoverage, $branchData->lineNumber, $branchData->blockNumber, $branchData->branchNumber, $branchData->taken);
			}

			$filename = Path::isAbsolute($sourceFile->getPathname())
				? Path::makeRelative($sourceFile->getPathname(), $workingDir)
				: Path::canonicalize($sourceFile->getPathname());

			return new SourceFile(str_replace("/", DIRECTORY_SEPARATOR, $filename), md5($source), $source, (array) $lineCoverage, $branchCoverage);
		});

		return new Job($sourceFiles->arr);
	}
}
