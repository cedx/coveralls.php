<?php declare(strict_types=1);
namespace Coveralls;

/** Represents a Git commit. */
class GitCommit implements \JsonSerializable {

  /** @var string The author mail address. */
  private string $authorEmail = '';

  /** @var string The author name. */
  private string $authorName = '';

  /** @var string The committer mail address. */
  private string $committerEmail = '';

  /** @var string The committer name. */
  private string $committerName = '';

  /** @var string The commit identifier. */
  private string $id;

  /** @var string The commit message. */
  private string $message;

  /**
   * Creates a new Git commit.
   * @param string $id The commit identifier.
   * @param string $message The commit message.
   */
  function __construct(string $id, string $message = '') {
    $this->id = $id;
    $this->message = $message;
  }

  /**
   * Creates a new Git commit from the specified JSON object.
   * @param object $map A JSON object representing a Git commit.
   * @return self The instance corresponding to the specified JSON object.
   */
  static function fromJson(object $map): self {
    return (new self(isset($map->id) && is_string($map->id) ? $map->id : '', isset($map->message) && is_string($map->message) ? $map->message : ''))
      ->setAuthorEmail(isset($map->author_email) && is_string($map->author_email) ? $map->author_email : '')
      ->setAuthorName(isset($map->author_name) && is_string($map->author_name) ? $map->author_name : '')
      ->setCommitterEmail(isset($map->committer_email) && is_string($map->committer_email) ? $map->committer_email : '')
      ->setCommitterName(isset($map->committer_name) && is_string($map->committer_name) ? $map->committer_name : '');
  }

  /**
   * Gets the author mail address.
   * @return string The author mail address.
   */
  function getAuthorEmail(): string {
    return $this->authorEmail;
  }

  /**
   * Gets the author name.
   * @return string The author name.
   */
  function getAuthorName(): string {
    return $this->authorName;
  }

  /**
   * Gets the committer mail address.
   * @return string The committer mail address.
   */
  function getCommitterEmail(): string {
    return $this->committerEmail;
  }

  /**
   * Gets the committer name.
   * @return string The committer name.
   */
  function getCommitterName(): string {
    return $this->committerName;
  }

  /**
   * Gets the commit identifier.
   * @return string The commit identifier.
   */
  function getId(): string {
    return $this->id;
  }

  /**
   * Gets the commit message.
   * @return string The commit message.
   */
  function getMessage(): string {
    return $this->message;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
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

  /**
   * Sets the author mail address.
   * @param string $value The new mail address.
   * @return $this This instance.
   */
  function setAuthorEmail(string $value): self {
    $this->authorEmail = $value;
    return $this;
  }

  /**
   * Sets the author name.
   * @param string $value The new name.
   * @return $this This instance.
   */
  function setAuthorName(string $value): self {
    $this->authorName = $value;
    return $this;
  }

  /**
   * Sets the committer mail address.
   * @param string $value The new mail address.
   * @return $this This instance.
   */
  function setCommitterEmail(string $value): self {
    $this->committerEmail = $value;
    return $this;
  }

  /**
   * Sets the committer name.
   * @param string $value The new name.
   * @return $this This instance.
   */
  function setCommitterName(string $value): self {
    $this->committerName = $value;
    return $this;
  }
}
