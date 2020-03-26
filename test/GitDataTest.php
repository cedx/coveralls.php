<?php declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isInstanceOf, isNull, logicalNot, logicalOr, matchesRegularExpression};

/** @testdox Coveralls\GitData */
class GitDataTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    // It should return an instance with default values for an empty map.
    $data = GitData::fromJson(new \stdClass);
    assertThat($data->getBranch(), isEmpty());
    assertThat($data->getCommit(), isNull());
    assertThat($data->getRemotes(), isEmpty());

    // It should return an initialized instance for a non-empty map.
    $data = GitData::fromJson((object) [
      'branch' => 'develop',
      'head' => (object) ['id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871'],
      'remotes' => [
        (object) ['name' => 'origin']
      ]
    ]);

    assertThat($data->getBranch(), equalTo('develop'));

    /** @var GitCommit $commit */
    $commit = $data->getCommit();
    assertThat($commit->getId(), equalTo('2ef7bde608ce5404e97d5f042f95f89f1c232871'));

    $remotes = $data->getRemotes();
    assertThat($remotes, countOf(1));

    /** @var GitRemote $remote */
    $remote = $remotes[0];
    assertThat($remote, isInstanceOf(GitRemote::class));
    assertThat($remote->getName(), equalTo('origin'));
  }

  /** @testdox ::fromRepository() */
  function testFromRepository(): void {
    // It should retrieve the Git data from the executable output.
    $data = GitData::fromRepository();
    assertThat($data->getBranch(), logicalNot(isEmpty()));

    /** @var GitCommit $commit */
    $commit = $data->getCommit();
    assertThat($commit->getId(), matchesRegularExpression('/^[a-f\d]{40}$/'));

    $remotes = $data->getRemotes();
    assertThat($remotes, logicalNot(isEmpty()));
    assertThat($remotes[0], isInstanceOf(GitRemote::class));

    /** @var GitRemote[] $origins */
    $origins = array_values(array_filter($remotes->getArrayCopy(), fn(GitRemote $remote) => $remote->getName() == 'origin'));
    assertThat($origins, countOf(1));
    assertThat((string) $origins[0]->getUrl(), logicalOr(
      equalTo('https://github.com/cedx/coveralls.php'),
      equalTo('https://github.com/cedx/coveralls.php.git')
    ));
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return a map with default values for a newly created instance.
    $map = (new GitData(new GitCommit('')))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(3));
    assertThat($map->branch, isEmpty());
    assertThat($map->head, isInstanceOf(\stdClass::class));
    assertThat($map->remotes, isEmpty());

    // It should return a non-empty map for an initialized instance.
    $map = (new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(3));
    assertThat($map->branch, equalTo('develop'));

    assertThat($map->head->id, equalTo('2ef7bde608ce5404e97d5f042f95f89f1c232871'));
    assertThat($map->remotes, countOf(1));
    assertThat($map->remotes[0]->name, equalTo('origin'));
  }
}
