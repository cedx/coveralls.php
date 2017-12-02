<?php
declare(strict_types=1);
namespace Coveralls\Parsers\Clover;

use Coveralls\{SourceFile};
use function PHPUnit\Expect\{expect, fail, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\Parsers\Clover\parseReport` function.
 */
class CloverTest extends TestCase {

  /**
   * Performs a common set of tasks just before the first test method is called.
   */
  public static function setUpBeforeClass(): void {
    require_once __DIR__.'/../../lib/Parsers/Clover.php';
  }

  /**
   * @test parseReport
   */
  public function testParseReport(): void {
    it('should properly parse Clover reports', function() {
      /** @var \Coveralls\Job $job */
      $job = parseReport(file_get_contents('test/fixtures/clover.xml'));
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

    it('should throw an exception if the Clover report is invalid or empty', function() {
      try {
        parseReport('<coverage><foo /></coverage>');
        fail('Exception not thrown.');
      }

      catch (\Throwable $e) {
        expect($e)->to->be->instanceOf(\InvalidArgumentException::class);
      }
    });
  }
}
