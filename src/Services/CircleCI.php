<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\Configuration;

/** Fetches the [CircleCI](https://circleci.com) configuration parameters from the environment. */
abstract class CircleCI {

	/** Reads the configuration parameters from the specified array of environment variables. */
	static function getConfiguration(array $env): Configuration {
		$nodes = (int) ($env["CIRCLE_NODE_TOTAL"] ?? "0");
		return new Configuration([
			"commit_sha" => $env["CIRCLE_SHA1"] ?? null,
			"parallel" => $nodes > 1 ? "true" : "false",
			"service_branch" => $env["CIRCLE_BRANCH"] ?? null,
			"service_build_url" => $env["CIRCLE_BUILD_URL"] ?? null,
			"service_job_number" => $env["CIRCLE_NODE_INDEX"] ?? null,
			"service_name" => "circleci",
			"service_number" => $env["CIRCLE_BUILD_NUM"] ?? null
		]);
	}
}
