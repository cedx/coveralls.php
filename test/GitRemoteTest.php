<?php
declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\GitRemote` class.
 */
class GitRemoteTest extends TestCase {

  /**
   * @test GitRemote::fromJson
   */
  public function testFromJson(): void {
    it('should return a null reference with a non-object value', function() {
      expect(GitRemote::fromJson('foo'))->to->be->null;
    });

    it('should return an instance with default values for an empty map', function() {
      $remote = GitRemote::fromJson([]);
      expect($remote)->to->be->instanceOf(GitRemote::class);
      expect($remote->getName())->to->be->empty;
      expect($remote->getUrl())->to->be->null;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $remote = GitRemote::fromJson(['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
      expect($remote)->to->be->instanceOf(GitRemote::class);
      expect($remote->getName())->to->equal('origin');
      expect((string) $remote->getUrl())->to->equal('https://github.com/cedx/coveralls.php.git');
    });
  }

  /**
   * @test GitRemote::jsonSerialize
   */
  public function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new GitRemote(''))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(2);
      expect($map->name)->to->be->empty;
      expect($map->url)->to->be->null;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git'))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(2);
      expect($map->name)->to->equal('origin');
      expect($map->url)->to->equal('https://github.com/cedx/coveralls.php.git');
    });
  }

  /**
   * @test GitRemote::__toString
   */
  public function testToString(): void {
    $remote = (string) new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git');

    it('should start with the class name', function() use ($remote) {
      expect($remote)->startWith('Coveralls\GitRemote {');
    });

    it('should contain the instance properties', function() use ($remote) {
      expect($remote)->to->contain('"name":"origin"')->and->contain('"url":"https://github.com/cedx/coveralls.php.git"');
    });
  }
}
