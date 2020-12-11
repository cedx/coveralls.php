<?php declare(strict_types=1);
namespace Coveralls;

/** Represents a Git commit. */
class GitCommit implements \JsonSerializable {

	/** The author mail address. */
	private string $authorEmail = "";

	/** The author name. */
	private string $authorName = "";

	/** The committer mail address. */
	private string $committerEmail = "";

	/** The committer name. */
	private string $committerName = "";

	/** Creates a new Git commit. */
	function __construct(private string $id, private string $message = "") {
		$this->id = $id;
		$this->message = $message;
	}

	/** Creates a new Git commit from the specified JSON object. */
	static function fromJson(object $map): self {
		return (new self(isset($map->id) && is_string($map->id) ? $map->id : "", isset($map->message) && is_string($map->message) ? $map->message : ""))
			->setAuthorEmail(isset($map->author_email) && is_string($map->author_email) ? $map->author_email : "")
			->setAuthorName(isset($map->author_name) && is_string($map->author_name) ? $map->author_name : "")
			->setCommitterEmail(isset($map->committer_email) && is_string($map->committer_email) ? $map->committer_email : "")
			->setCommitterName(isset($map->committer_name) && is_string($map->committer_name) ? $map->committer_name : "");
	}

	/** Gets the author mail address. */
	function getAuthorEmail(): string {
		return $this->authorEmail;
	}

	/** Gets the author name. */
	function getAuthorName(): string {
		return $this->authorName;
	}

	/** Gets the committer mail address. */
	function getCommitterEmail(): string {
		return $this->committerEmail;
	}

	/** Gets the committer name. */
	function getCommitterName(): string {
		return $this->committerName;
	}

	/** Gets the commit identifier. */
	function getId(): string {
		return $this->id;
	}

	/** Gets the commit message. */
	function getMessage(): string {
		return $this->message;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->id = $this->getId();
		if (mb_strlen($authorEmail = $this->getAuthorEmail())) $map->author_email = $authorEmail;
		if (mb_strlen($authorName = $this->getAuthorName())) $map->author_name = $authorName;
		if (mb_strlen($committerEmail = $this->getCommitterEmail())) $map->committer_email = $committerEmail;
		if (mb_strlen($committerName = $this->getCommitterName())) $map->committer_name = $committerName;
		if (mb_strlen($message = $this->getMessage())) $map->message = $message;
		return $map;
	}

	/** Sets the author mail address. */
	function setAuthorEmail(string $value): static {
		$this->authorEmail = $value;
		return $this;
	}

	/** Sets the author name. */
	function setAuthorName(string $value): static {
		$this->authorName = $value;
		return $this;
	}

	/** Sets the committer mail address. */
	function setCommitterEmail(string $value): static {
		$this->committerEmail = $value;
		return $this;
	}

	/** Sets the committer name. */
	function setCommitterName(string $value): static {
		$this->committerName = $value;
		return $this;
	}
}
