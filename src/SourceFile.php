<?php declare(strict_types=1);
namespace Coveralls;

/** Represents a source code file and its coverage data for a single job. */
class SourceFile implements \JsonSerializable {

	/** The branch data for this file's job. */
	private \ArrayObject $branches;

	/** The coverage data for this file's job. */
	private \ArrayObject $coverage;

	/** Creates a new source file. */
	function __construct(private string $name, private string $sourceDigest, private string $source = "", array $coverage = [], array $branches = []) {
		$this->branches = new \ArrayObject($branches);
		$this->coverage = new \ArrayObject($coverage);
	}

	/** Creates a new source file from the specified JSON object. */
	static function fromJson(object $map): self {
		return new self(
			isset($map->name) && is_string($map->name) ? $map->name : "",
			isset($map->source_digest) && is_string($map->source_digest) ? $map->source_digest : "",
			isset($map->source) && is_string($map->source) ? $map->source : "",
			isset($map->coverage) && is_array($map->coverage) ? $map->coverage : [],
			isset($map->branches) && is_array($map->branches) ? $map->branches : []
		);
	}

	/** Gets the branch data for this file's job. */
	function getBranches(): \ArrayObject {
		return $this->branches;
	}

	/** Gets the coverage data for this file's job. */
	function getCoverage(): \ArrayObject {
		return $this->coverage;
	}

	/** Gets the file path of this source file. */
	function getName(): string {
		return $this->name;
	}

	/** Gets the contents of this source file. */
	function getSource(): string {
		return $this->source;
	}

	/** Gets the MD5 digest of the full source code of this file. */
	function getSourceDigest(): string {
		return $this->sourceDigest;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->coverage = (array) $this->getCoverage();
		$map->name = $this->getName();
		$map->source_digest = $this->getSourceDigest();
		if (count($branches = $this->getBranches())) $map->branches = (array) $branches;
		if (mb_strlen($source = $this->getSource())) $map->source = $source;
		return $map;
	}
}
