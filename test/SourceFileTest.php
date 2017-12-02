<?php
declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\SourceFile` class.
 */
class SourceFileTest extends TestCase {

  /**
   * @test SourceFile::fromJson
   */
  public function testFromJson(): void {
    it('should return a null reference with a non-object value', function() {
      expect(SourceFile::fromJson('foo'))->to->be->null;
    });

    it('should return an instance with default values for an empty map', function() {
      $file = SourceFile::fromJson([]);
      expect($file)->to->be->instanceOf(SourceFile::class);

      expect($file->getCoverage())->to->be->empty;
      expect($file->getName())->to->be->empty;
      expect($file->getSource())->to->be->empty;
      expect($file->getSourceDigest())->to->be->empty;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $file = SourceFile::fromJson([
        'coverage' => [null, 2, 0, null, 4, 15, null],
        'name' => 'coveralls.php',
        'source' => 'function main() {}',
        'source_digest' => 'e23fb141da9a7b438479a48eac7b7249'
      ]);

      expect($file)->to->be->instanceOf(SourceFile::class);

      $coverage = $file->getCoverage();
      expect($coverage)->to->have->lengthOf(7);
      expect($coverage[0])->to->be->null;
      expect($coverage[1])->to->equal(2);

      expect($file->getName())->to->equal('coveralls.php');
      expect($file->getSource())->to->equal('function main() {}');
      expect($file->getSourceDigest())->to->equal('e23fb141da9a7b438479a48eac7b7249');
    });
  }

  /**
   * @test SourceFile::jsonSerialize
   */
  public function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new SourceFile('', ''))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(3);

      expect($map->coverage)->to->be->an('array')->and->be->empty;
      expect($map->name)->to->be->empty;
      expect($map->source_digest)->to->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new SourceFile(
        'coveralls.php',
        'e23fb141da9a7b438479a48eac7b7249',
        'function main() {}',
        [null, 2, 0, null, 4, 15, null]
      ))->jsonSerialize();

      expect(get_object_vars($map))->to->have->lengthOf(4);

      expect($map->coverage)->to->be->an('array')->and->have->lengthOf(7);
      expect($map->coverage[0])->to->be->null;
      expect($map->coverage[1])->to->equal(2);

      expect($map->name)->to->equal('coveralls.php');
      expect($map->source)->to->equal('function main() {}');
      expect($map->source_digest)->to->equal('e23fb141da9a7b438479a48eac7b7249');
    });
  }

  /**
   * @test SourceFile::__toString
   */
  public function testToString(): void {
    $remote = (string) new SourceFile('coveralls.php', 'e23fb141da9a7b438479a48eac7b7249', 'function main() {}', [null, 2, 0, null, 4, 15, null]);

    it('should start with the class name', function() use ($remote) {
      expect($remote)->startWith('Coveralls\SourceFile {');
    });

    it('should contain the instance properties', function() use ($remote) {
      expect($remote)->to->contain('"name":"coveralls.php"')
        ->and->contain('"source":"function main() {}"')
        ->and->contain('"source_digest":"e23fb141da9a7b438479a48eac7b7249"');
    });
  }
}
