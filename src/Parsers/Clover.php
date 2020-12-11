<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{Job, SourceFile};
use Webmozart\PathUtil\Path;

/** Parses [Clover](https://www.atlassian.com/software/clover) coverage reports. */
abstract class Clover {

	/** Parses the specified coverage report. */
	static function parseReport(string $report): Job {
		/** @var \SimpleXMLElement $xml */
		$xml = @simplexml_load_string($report);
		($xml && $xml->count() && $xml->project->count()) || throw new \InvalidArgumentException("The specified Clover report is invalid.");

		$files = [...($xml->xpath("/coverage/project/file") ?: []), ...($xml->xpath("/coverage/project/package/file") ?: [])];
		$workingDir = (string) getcwd();

		return new Job(array_map(function(\SimpleXMLElement $file) use ($workingDir) {
			isset($file["name"]) || throw new \InvalidArgumentException("Invalid file data: {$file->asXML()}");

			$sourceFile = new \SplFileObject((string) $file["name"]);
			$sourceFile->isReadable() || throw new \RuntimeException("Source file not found: {$sourceFile->getPathname()}");

			$source = (string) $sourceFile->fread((int) $sourceFile->getSize());
			mb_strlen($source) || throw new \RuntimeException("Source file empty: {$sourceFile->getPathname()}");

			$coverage = new \SplFixedArray(count(preg_split('/\r?\n/', $source) ?: []));
			foreach ($file->line as $line) {
				if (!isset($line["type"]) || (string) $line["type"] != "stmt") continue;
				$lineNumber = max(1, (int) $line["num"]);
				$coverage[$lineNumber - 1] = max(0, (int) $line["count"]);
			}

			$filename = Path::isAbsolute($sourceFile->getPathname())
				? Path::makeRelative($sourceFile->getPathname(), $workingDir)
				: Path::canonicalize($sourceFile->getPathname());

			return new SourceFile($filename, md5($source), $source, (array) $coverage);
		}, $files));
	}
}
