<?php
declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\GitCommit` class.
 */
class GitCommitTest extends TestCase {

  /**
   * @test GitCommit::fromJson
   */
  public function testFromJson(): void {
    // It should return a null reference with a non-object value.
    assertThat(GitCommit::fromJson('foo'), isNull());

    // It should return an instance with default values for an empty map.
    $commit = GitCommit::fromJson([]);
    assertThat($commit, isInstanceOf(GitCommit::class));

    assertThat($commit->getAuthorEmail(), isEmpty());
    assertThat($commit->getAuthorName(), isEmpty());
    assertThat($commit->getId(), isEmpty());
    assertThat($commit->getMessage(), isEmpty());

    // It should return an initialized instance for a non-empty map.
    $commit = GitCommit::fromJson([
      'author_email' => 'anonymous@secret.com',
      'author_name' => 'Anonymous',
      'id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871',
      'message' => 'Hello World!'
    ]);

    assertThat($commit, isInstanceOf(GitCommit::class));
    assertThat($commit->getAuthorEmail(), equalTo('anonymous@secret.com'));
    assertThat($commit->getAuthorName(), equalTo('Anonymous'));
    assertThat($commit->getId(), equalTo('2ef7bde608ce5404e97d5f042f95f89f1c232871'));
    assertThat($commit->getMessage(), equalTo('Hello World!'));
  }

  /**
   * @test GitCommit::jsonSerialize
   */
  public function testJsonSerialize(): void {
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

  /**
   * @test GitCommit::__toString
   */
  public function testToString(): void {
    $commit = (string) (new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871', 'Hello World!'))
      ->setAuthorEmail('anonymous@secret.com')
      ->setAuthorName('Anonymous');

    // It should start with the class name.
    assertThat($commit, stringStartsWith('Coveralls\GitCommit {'));

    // It should contain the instance properties.
    assertThat($commit, logicalAnd(
      stringContains('"author_email":"anonymous@secret.com"'),
      stringContains('"author_name":"Anonymous"'),
      stringContains('"id":"2ef7bde608ce5404e97d5f042f95f89f1c232871"'),
      stringContains('"message":"Hello World!"')
    ));
  }
}
