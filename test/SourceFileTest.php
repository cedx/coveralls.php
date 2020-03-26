<?php declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull, isType, logicalAnd};

/** @testdox Coveralls\SourceFile */
class SourceFileTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    // It should return an instance with default values for an empty map.
    $file = SourceFile::fromJson(new \stdClass);
    assertThat($file->getCoverage(), isEmpty());
    assertThat($file->getName(), isEmpty());
    assertThat($file->getSource(), isEmpty());
    assertThat($file->getSourceDigest(), isEmpty());

    // It should return an initialized instance for a non-empty map.
    $file = SourceFile::fromJson((object) [
      'coverage' => [null, 2, 0, null, 4, 15, null],
      'name' => 'coveralls.php',
      'source' => 'function main() {}',
      'source_digest' => 'e23fb141da9a7b438479a48eac7b7249'
    ]);

    $coverage = $file->getCoverage();
    assertThat($coverage, countOf(7));
    assertThat($coverage[0], isNull());
    assertThat($coverage[1], equalTo(2));
    assertThat($file->getName(), equalTo('coveralls.php'));
    assertThat($file->getSource(), equalTo('function main() {}'));
    assertThat($file->getSourceDigest(), equalTo('e23fb141da9a7b438479a48eac7b7249'));
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return a map with default values for a newly created instance.
    $map = (new SourceFile('', ''))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(3));
    assertThat($map->coverage, isEmpty());
    assertThat($map->name, isEmpty());
    assertThat($map->source_digest, isEmpty());

    // It should return a non-empty map for an initialized instance.
    $map = (new SourceFile(
      'coveralls.php',
      'e23fb141da9a7b438479a48eac7b7249',
      'function main() {}',
      [null, 2, 0, null, 4, 15, null]
    ))->jsonSerialize();

    assertThat(get_object_vars($map), countOf(4));
    assertThat($map->coverage, logicalAnd(isType('array'), countOf(7)));
    assertThat($map->coverage[0], isNull());
    assertThat($map->coverage[1], equalTo(2));
    assertThat($map->name, equalTo('coveralls.php'));
    assertThat($map->source, equalTo('function main() {}'));
    assertThat($map->source_digest, equalTo('e23fb141da9a7b438479a48eac7b7249'));
  }
}
