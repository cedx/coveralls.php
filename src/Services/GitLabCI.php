<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\Configuration;

/** Fetches the [GitLab CI](https://gitlab.com) configuration parameters from the environment. */
abstract class GitLabCI {

	/** Reads the configuration parameters from the specified array of environment variables. */
	static function getConfiguration(array $env): Configuration {
		return new Configuration([
			"commit_sha" => $env["CI_BUILD_REF"] ?? null,
			"service_branch" => $env["CI_BUILD_REF_NAME"] ?? null,
			"service_job_id" => $env["CI_BUILD_ID"] ?? null,
			"service_job_number" => $env["CI_BUILD_NAME"] ?? null,
			"service_name" => "gitlab-ci"
		]);
	}
}
