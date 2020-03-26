<?php declare(strict_types=1);
namespace Coveralls;

use Nyholm\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull};

/** @testdox Coveralls\GitRemote */
class GitRemoteTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    // It should return an instance with default values for an empty map.
    $remote = GitRemote::fromJson(new \stdClass);
    assertThat($remote->getName(), isEmpty());
    assertThat($remote->getUrl(), isNull());

    // It should return an initialized instance for a non-empty map.
    $remote = GitRemote::fromJson((object) ['name' => 'origin', 'url' => 'git@github.com:cedx/coveralls.php.git']);
    assertThat($remote->getName(), equalTo('origin'));
    assertThat((string) $remote->getUrl(), equalTo('ssh://git@github.com/cedx/coveralls.php.git'));

    $remote = GitRemote::fromJson((object) ['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
    assertThat((string) $remote->getUrl(), equalTo('https://github.com/cedx/coveralls.php.git'));
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return a map with default values for a newly created instance.
    $map = (new GitRemote(''))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(2));
    assertThat($map->name, isEmpty());
    assertThat($map->url, isNull());

    // It should return a non-empty map for an initialized instance.
    $map = (new GitRemote('origin', 'git@github.com:cedx/coveralls.php.git'))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(2));
    assertThat($map->name, equalTo('origin'));
    assertThat($map->url, equalTo('ssh://git@github.com/cedx/coveralls.php.git'));

    $map = (new GitRemote('origin', new Uri('https://github.com/cedx/coveralls.php.git')))->jsonSerialize();
    assertThat($map->url, equalTo('https://github.com/cedx/coveralls.php.git'));
  }
}
