<?php declare(strict_types=1);
namespace Coveralls;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/** Represents a Git remote repository. */
class GitRemote implements \JsonSerializable {

	/** The remote's URL. */
	private ?UriInterface $url;

	/** Creates a new Git remote repository. */
	function __construct(private string $name, UriInterface|string|null $url = null) {
		$this->url = !is_string($url)
			? $url
			: new Uri(preg_match('#^\w+://#', $url) ? $url : (string) preg_replace('/^([^@]+@)?([^:]+):(.+)$/', 'ssh://$1$2/$3', $url));
	}

	/** Creates a new remote repository from the specified JSON object. */
	static function fromJson(object $map): self {
		return new self(
			isset($map->name) && is_string($map->name) ? $map->name : "",
			isset($map->url) && is_string($map->url) ? $map->url : null
		);
	}

	/** Gets the name of this remote. */
	function getName(): string {
		return $this->name;
	}

	/** Gets the URL of this remote. */
	function getUrl(): ?UriInterface {
		return $this->url;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		return (object) [
			"name" => $this->getName(),
			"url" => ($url = $this->getUrl()) ? (string) $url : null
		];
	}
}
