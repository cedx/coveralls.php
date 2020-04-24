<?php declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isFalse, isInstanceOf, isNull, isTrue};

/** @testdox Coveralls\Job */
class JobTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    // It should return an instance with default values for an empty map.
    $job = Job::fromJson(new \stdClass);
    assertThat($job->getGit(), isNull());
    assertThat($job->isParallel(), isFalse());
    assertThat($job->getRepoToken(), isEmpty());
    assertThat($job->getRunAt(), isNull());
    assertThat($job->getSourceFiles(), isEmpty());

    // It should return an initialized instance for a non-empty map.
    $job = Job::fromJson((object) [
      'git' => (object) ['branch' => 'develop'],
      'parallel' => true,
      'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
      'run_at' => '2017-01-29T03:43:30+01:00',
      'source_files' => [
        (object) ['name' => '/home/cedx/coveralls.php']
      ]
    ]);

    assertThat($job->isParallel(), isTrue());
    assertThat($job->getRepoToken(), equalTo('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt'));

    /** @var GitData $git */
    $git = $job->getGit();
    assertThat($git->getBranch(), equalTo('develop'));

    /** @var \DateTimeInterface $runAt */
    $runAt = $job->getRunAt();
    assertThat($runAt->format('c'), equalTo('2017-01-29T03:43:30+01:00'));

    $sourceFiles = $job->getSourceFiles();
    assertThat($sourceFiles, countOf(1));

    /** @var SourceFile $sourceFile */
    [$sourceFile] = $sourceFiles;
    assertThat($sourceFile, isInstanceOf(SourceFile::class));
    assertThat($sourceFile->getName(), equalTo('/home/cedx/coveralls.php'));
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return a map with default values for a newly created instance.
    $map = (new Job)->jsonSerialize();
    assertThat(get_object_vars($map), countOf(1));
    assertThat($map->source_files, isEmpty());

    // It should return a non-empty map for an initialized instance.
    $map = (new Job([new SourceFile('/home/cedx/coveralls.php', '')]))
      ->setGit(new GitData(new GitCommit(''), 'develop'))
      ->setParallel(true)
      ->setRepoToken('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt')
      ->setRunAt(new \DateTimeImmutable('2017-01-29T03:43:30+01:00'))
      ->jsonSerialize();

    assertThat(get_object_vars($map), countOf(5));
    assertThat($map->parallel, isTrue());
    assertThat($map->repo_token, equalTo('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt'));
    assertThat($map->run_at, equalTo('2017-01-29T03:43:30+01:00'));

    assertThat($map->git->branch, equalTo('develop'));
    assertThat($map->source_files, countOf(1));
    assertThat($map->source_files[0]->name, equalTo('/home/cedx/coveralls.php'));
  }
}
