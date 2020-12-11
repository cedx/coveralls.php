<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\Configuration;

/** Fetches the [Semaphore](https://semaphoreci.com) configuration parameters from the environment. */
abstract class Semaphore {

	/** Reads the configuration parameters from the specified array of environment variables. */
	static function getConfiguration(array $env): Configuration {
		return new Configuration([
			"commit_sha" => $env["REVISION"] ?? null,
			"service_branch" => $env["BRANCH_NAME"] ?? null,
			"service_name" => "semaphore",
			"service_number" => $env["SEMAPHORE_BUILD_NUMBER"] ?? null,
			"service_pull_request" => $env["PULL_REQUEST_NUMBER"] ?? null
		]);
	}
}
