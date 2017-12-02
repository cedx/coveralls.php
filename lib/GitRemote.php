<?php
declare(strict_types=1);
namespace Coveralls;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

/**
 * Represents a Git remote repository.
 */
class GitRemote implements \JsonSerializable {

  /**
   * @var string The remote's name.
   */
  private $name;

  /**
   * @var Uri The remote's URL.
   */
  private $url;

  /**
   * Initializes a new instance of the class.
   * @param string $name The remote's name.
   * @param string|UriInterface $url The remote's URL.
   */
  public function __construct(string $name, $url = null) {
    $this->name = $name;
    $this->url = is_string($url) ? new Uri($url) : $url;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Creates a new remote repository from the specified JSON map.
   * @param mixed $map A JSON map representing a remote repository.
   * @return GitRemote The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJson($map): ?self {
    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : new static(
      isset($map->name) && is_string($map->name) ? $map->name : '',
      isset($map->url) && is_string($map->url) ? $map->url : null
    );
  }

  /**
   * Gets the name of this remote.
   * @return string The remote's name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Gets the URL of this remote.
   * @return UriInterface The remote's URL.
   */
  public function getUrl(): ?UriInterface {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      'name' => $this->getName(),
      'url' => ($url = $this->getUrl()) ? (string) $url : null
    ];
  }
}
