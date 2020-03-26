<?php declare(strict_types=1);
namespace Coveralls\Cli;

use Coveralls\{Client};
use Nyholm\Psr7\{Uri};
use Symfony\Component\Console\Exception\{RuntimeException};
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\{OutputInterface};

/** The console command. */
class Command extends \Symfony\Component\Console\Command\Command {

  /** @var string The command name. */
  protected static $defaultName = 'coveralls';

  /** Configures the current command. */
  protected function configure(): void {
    $this
      ->setDescription('Send a coverage report to the Coveralls service.')
      ->addArgument('file', InputArgument::REQUIRED, 'The path of the coverage report to upload');
  }

  /**
   * Executes the current command.
   * @param InputInterface $input The input arguments and options.
   * @param OutputInterface $output The console output.
   * @return int The exit code.
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    /** @var string $file */
    $file = $input->getArgument('file');
    if (!is_file($file)) throw new RuntimeException("File not found: $file");

    $client = new Client(new Uri($_SERVER['COVERALLS_ENDPOINT'] ?? Client::defaultEndPoint));
    $output->writeln("[Coveralls] Submitting to {$client->getEndPoint()}");
    $client->upload((string) @file_get_contents($file));
    return 0;
  }
}
