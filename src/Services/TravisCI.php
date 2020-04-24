<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Travis CI](https://travis-ci.com) configuration parameters from the environment. */
abstract class TravisCI {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    $config = new Configuration([
      'commit_sha' => $env['TRAVIS_COMMIT'] ?? null,
      'flag_name' => $env['TRAVIS_JOB_NAME'] ?? null,
      'git_message' => $env['TRAVIS_COMMIT_MESSAGE'] ?? null,
      'service_branch' => $env['TRAVIS_BRANCH'] ?? null,
      'service_build_url' => $env['TRAVIS_BUILD_WEB_URL'] ?? null,
      'service_job_id' => $env['TRAVIS_JOB_ID'] ?? null,
      'service_name' => 'travis-ci'
    ]);

    $pullRequest = $env['TRAVIS_PULL_REQUEST'] ?? null;
    if ($pullRequest && $pullRequest != 'false') $config['service_pull_request'] = $pullRequest;

    return $config;
  }
}
