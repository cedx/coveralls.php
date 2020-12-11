<?php declare(strict_types=1);
namespace Coveralls\Cli;

use Coveralls\Client;
use Nyholm\Psr7\Uri;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

/** The console command. */
class Command extends \Symfony\Component\Console\Command\Command {

	/** The command name. */
	protected static $defaultName = "coveralls";

	/** Configures the current command. */
	protected function configure(): void {
		$this
			->setDescription("Send a coverage report to the Coveralls service.")
			->addArgument("file", InputArgument::REQUIRED, "The path of the coverage report to upload");
	}

	/** Executes the current command, and returns the exit code. */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		/** @var string $path */
		$path = $input->getArgument("file");
		$file = new \SplFileObject($path);
		$file->isReadable() || throw new RuntimeException("File not found: {$file->getPathname()}");

		$client = new Client(new Uri($_SERVER["COVERALLS_ENDPOINT"] ?? Client::defaultEndPoint));
		$output->writeln("[Coveralls] Submitting to {$client->getEndPoint()}");
		$client->upload((string) $file->fread((int) $file->getSize()));
		return 0;
	}
}
