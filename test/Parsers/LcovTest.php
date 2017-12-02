<?php
declare(strict_types=1);
namespace Coveralls\Parsers\Lcov;

use Coveralls\{SourceFile};
use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\Parsers\Lcov\parseReport` function.
 */
class LcovTest extends TestCase {

  /**
   * Performs a common set of tasks just before the first test method is called.
   */
  public static function setUpBeforeClass(): void {
    require_once __DIR__.'/../../lib/Parsers/Lcov.php';
  }

  /**
   * @test parseReport
   */
  public function testParseReport(): void {
    it('should properly parse LCOV reports', function() {
      /** @var \Coveralls\Job $job */
      $job = parseReport(file_get_contents('test/fixtures/lcov.info'));
      $files = $job->getSourceFiles();
      expect($files)->to->have->lengthOf(3);

      $subset = [null, 2, 2, 2, 2, null];
      expect($files[0])->to->be->instanceOf(SourceFile::class);
      expect($files[0]->getName())->to->equal('lib/Client.php');
      expect($files[0]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()))->to->equal($subset);

      $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
      expect($files[1]->getName())->to->equal('lib/Configuration.php');
      expect($files[1]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()))->to->equal($subset);

      $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
      expect($files[2]->getName())->to->equal('lib/GitCommit.php');
      expect($files[2]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()))->to->equal($subset);
    });
  }
}
