<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{Job, SourceFile};
use lcov\{Record, Report};
use Webmozart\PathUtil\Path;

/** Parses [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage reports. */
abstract class Lcov {

	/** Parses the specified coverage report. */
	static function parseReport(string $report): Job {
		$workingDir = (string) getcwd();
		$sourceFiles = Report::fromCoverage($report)->records->map(function(Record $record) use ($workingDir) {
			$sourceFile = new \SplFileObject($record->sourceFile);
			$sourceFile->isReadable() || throw new \RuntimeException("Source file not found: {$sourceFile->getPathname()}");

			$source = (string) $sourceFile->fread((int) $sourceFile->getSize());
			mb_strlen($source) || throw new \RuntimeException("Source file empty: {$sourceFile->getPathname()}");

			/** @var \lcov\LineCoverage|null $lines */
			$lines = $record->lines;

			$lineCoverage = new \SplFixedArray(count(preg_split('/\r?\n/', $source) ?: []));
			if ($lines) foreach ($lines->data as $lineData) {
				/** @var int $offset */
				$offset = $lineData->lineNumber - 1;
				$lineCoverage[$offset] = $lineData->executionCount;
			}

			/** @var \lcov\BranchCoverage|null $branches */
			$branches = $record->branches;
			$branchCoverage = [];
			if ($branches) foreach ($branches->data as $branchData)
				array_push($branchCoverage, $branchData->lineNumber, $branchData->blockNumber, $branchData->branchNumber, $branchData->taken);

			$filename = Path::isAbsolute($sourceFile->getPathname())
				? Path::makeRelative($sourceFile->getPathname(), $workingDir)
				: Path::canonicalize($sourceFile->getPathname());

			return new SourceFile($filename, md5($source), $source, (array) $lineCoverage, $branchCoverage);
		});

		return new Job($sourceFiles->arr);
	}
}
