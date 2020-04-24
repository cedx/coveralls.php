<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Jenkins](https://jenkins.io) configuration parameters from the environment. */
abstract class Jenkins {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    return new Configuration([
      'commit_sha' => $env['GIT_COMMIT'] ?? null,
      'service_branch' => $env['GIT_BRANCH'] ?? null,
      'service_build_url' => $env['BUILD_URL'] ?? null,
      'service_job_id' => $env['BUILD_ID'] ?? null,
      'service_name' => 'jenkins',
      'service_number' => $env['BUILD_NUMBER'] ?? null,
      'service_pull_request' => $env['ghprbPullId'] ?? null
    ]);
  }
}
