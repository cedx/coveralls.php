<?php
declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\GitData` class.
 */
class GitDataTest extends TestCase {

  /**
   * @test GitData::fromJson
   */
  public function testFromJson(): void {
    // It should return a null reference with a non-object value.
    assertThat(GitData::fromJson('foo'), isNull());

    // It should return an instance with default values for an empty map.
    $data = GitData::fromJson([]);
    assertThat($data, isInstanceOf(GitData::class));
    assertThat($data->getBranch(), isEmpty());
    assertThat($data->getCommit(), isInstanceOf(GitCommit::class));
    assertThat($data->getRemotes(), isEmpty());

    // It should return an initialized instance for a non-empty map.
    $data = GitData::fromJson([
      'branch' => 'develop',
      'head' => ['id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871'],
      'remotes' => [
        ['name' => 'origin']
      ]
    ]);

    assertThat($data, isInstanceOf(GitData::class));
    assertThat($data->getBranch(), equalTo('develop'));

    $commit = $data->getCommit();
    assertThat($commit, isInstanceOf(GitCommit::class));
    assertThat($commit->getId(), equalTo('2ef7bde608ce5404e97d5f042f95f89f1c232871'));

    $remotes = $data->getRemotes();
    assertThat($remotes, countOf(1));
    assertThat($remotes[0], isInstanceOf(GitRemote::class));
    assertThat($remotes[0]->getName(), equalTo('origin'));
  }

  /**
   * @test GitData::fromRepository
   */
  public function testFromRepository(): void {
    // It should retrieve the Git data from the executable output.
    $data = GitData::fromRepository();
    assertThat($data->getBranch(), logicalNot(isEmpty()));

    $commit = $data->getCommit();
    assertThat($commit, isInstanceOf(GitCommit::class));
    assertThat($commit->getId(), matchesRegularExpression('/^[a-f\d]{40}$/'));

    $remotes = $data->getRemotes();
    assertThat($remotes, logicalNot(isEmpty()));
    assertThat($remotes[0], isInstanceOf(GitRemote::class));

    /** @var GitRemote[] $origin */
    $origin = array_values(array_filter($remotes->getArrayCopy(), function(GitRemote $remote): bool {
      return $remote->getName() == 'origin';
    }));

    assertThat($origin, countOf(1));
    assertThat((string) $origin[0]->getUrl(), equalTo('https://github.com/cedx/coveralls.php.git'));
  }

  /**
   * @test GitData::jsonSerialize
   */
  public function testJsonSerialize(): void {
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

    assertThat($map->head, attributeEqualTo('id', '2ef7bde608ce5404e97d5f042f95f89f1c232871'));
    assertThat($map->remotes, countOf(1));
    assertThat($map->remotes[0], attributeEqualTo('name', 'origin'));
  }

  /**
   * @test GitData::__toString
   */
  public function testToString(): void {
    $data = (string) new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]);

    // It should start with the class name.
    assertThat($data, stringStartsWith('Coveralls\GitData {'));

    // It should contain the instance properties.
    assertThat($data, logicalAnd(
      stringContains('"branch":"develop"'),
      stringContains('"head":{'),
      stringContains('"remotes":[{')
    ));
  }
}
