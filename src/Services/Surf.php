<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\Configuration;

/** Fetches the [Surf](https://github.com/surf-build/surf) configuration parameters from the environment. */
abstract class Surf {

	/** Reads the configuration parameters from the specified array of environment variables. */
	static function getConfiguration(array $env): Configuration {
		return new Configuration([
			"commit_sha" => $env["SURF_SHA1"] ?? null,
			"service_branch" => $env["SURF_REF"] ?? null,
			"service_name" => "surf"
		]);
	}
}
