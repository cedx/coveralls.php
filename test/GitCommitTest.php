<?php declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty};

/** @testdox Coveralls\GitCommit */
class GitCommitTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    // It should return an instance with default values for an empty map.
    $commit = GitCommit::fromJson(new \stdClass);
    assertThat($commit->getAuthorEmail(), isEmpty());
    assertThat($commit->getAuthorName(), isEmpty());
    assertThat($commit->getId(), isEmpty());
    assertThat($commit->getMessage(), isEmpty());

    // It should return an initialized instance for a non-empty map.
    $commit = GitCommit::fromJson((object) [
      'author_email' => 'anonymous@secret.com',
      'author_name' => 'Anonymous',
      'id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871',
      'message' => 'Hello World!'
    ]);

    assertThat($commit->getAuthorEmail(), equalTo('anonymous@secret.com'));
    assertThat($commit->getAuthorName(), equalTo('Anonymous'));
    assertThat($commit->getId(), equalTo('2ef7bde608ce5404e97d5f042f95f89f1c232871'));
    assertThat($commit->getMessage(), equalTo('Hello World!'));
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return a map with default values for a newly created instance.
    $map = (new GitCommit(''))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(1));
    assertThat($map->id, isEmpty());

    // It should return a non-empty map for an initialized instance.
    $map = (new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871', 'Hello World!'))
      ->setAuthorEmail('anonymous@secret.com')
      ->setAuthorName('Anonymous')
      ->jsonSerialize();

    assertThat(get_object_vars($map), countOf(4));
    assertThat($map->author_email, equalTo('anonymous@secret.com'));
    assertThat($map->author_name, equalTo('Anonymous'));
    assertThat($map->id, equalTo('2ef7bde608ce5404e97d5f042f95f89f1c232871'));
    assertThat($map->message, equalTo('Hello World!'));
  }
}
