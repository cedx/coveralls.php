<?php
declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\Job` class.
 */
class JobTest extends TestCase {

  /**
   * @test Job::fromJson
   */
  public function testFromJson(): void {
    it('should return a null reference with a non-object value', function() {
      expect(Job::fromJson('foo'))->to->be->null;
    });

    it('should return an instance with default values for an empty map', function() {
      $job = Job::fromJson([]);
      expect($job)->to->be->instanceOf(Job::class);

      expect($job->getGit())->to->be->null;
      expect($job->isParallel())->to->be->false;
      expect($job->getRepoToken())->to->be->empty;
      expect($job->getRunAt())->to->be->null;
      expect($job->getSourceFiles())->to->be->empty;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $job = Job::fromJson([
        'git' => ['branch' => 'develop'],
        'parallel' => true,
        'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
        'run_at' => '2017-01-29T03:43:30+01:00',
        'source_files' => [
          ['name' => '/home/cedx/coveralls.php']
        ]
      ]);

      expect($job)->to->be->instanceOf(Job::class);
      expect($job->isParallel())->to->be->true;
      expect($job->getRepoToken())->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');

      $git = $job->getGit();
      expect($git)->to->be->instanceOf(GitData::class);
      expect($git->getBranch())->to->equal('develop');

      $runAt = $job->getRunAt();
      expect($runAt)->to->be->instanceOf(\DateTime::class);
      expect($runAt->format('c'))->to->equal('2017-01-29T03:43:30+01:00');

      $sourceFiles = $job->getSourceFiles();
      expect($sourceFiles)->to->have->lengthOf(1);
      expect($sourceFiles[0])->to->be->instanceOf(SourceFile::class);
      expect($sourceFiles[0]->getName())->to->equal('/home/cedx/coveralls.php');
    });
  }

  /**
   * @test Job::jsonSerialize
   */
  public function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new Job)->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(1);
      expect($map->source_files)->to->be->an('array')->and->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new Job([new SourceFile('/home/cedx/coveralls.php', '')]))
        ->setGit(new GitData(new GitCommit(''), 'develop'))
        ->setParallel(true)
        ->setRepoToken('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt')
        ->setRunAt('2017-01-29T03:43:30+01:00')
        ->jsonSerialize();

      expect(get_object_vars($map))->to->have->lengthOf(5);
      expect($map->parallel)->to->be->true;
      expect($map->repo_token)->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');
      expect($map->run_at)->to->equal('2017-01-29T03:43:30+01:00');

      expect($map->git)->to->be->an('object');
      expect($map->git->branch)->to->equal('develop');

      expect($map->source_files)->to->be->an('array')->and->have->lengthOf(1);
      expect($map->source_files[0])->to->be->an('object');
      expect($map->source_files[0]->name)->to->equal('/home/cedx/coveralls.php');
    });
  }

  /**
   * @test Job::__toString
   */
  public function testToString(): void {
    $job = (string) (new Job([new SourceFile('/home/cedx/coveralls.php', '')]))
      ->setGit(new GitData(new GitCommit(''), 'develop'))
      ->setParallel(true)
      ->setRepoToken('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt')
      ->setRunAt('2017-01-29T03:43:30+01:00');

    it('should start with the class name', function() use ($job) {
      expect($job)->startWith('Coveralls\Job {');
    });

    it('should contain the instance properties', function() use ($job) {
      expect($job)->to->contain('"git":{')
        ->and->contain('"parallel":true')
        ->and->contain('"repo_token":"yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt"')
        ->and->contain('"run_at":"2017-01-29T03:43:30+01:00"')
        ->and->contain('"source_files":[{');
    });
  }
}
