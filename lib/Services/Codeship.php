<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Codeship](https://circleci.com) configuration parameters from the environment. */
abstract class Codeship {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    return new Configuration([
      'commit_sha' => $env['CI_COMMIT_ID'] ?? null,
      'git_committer_email' => $env['CI_COMMITTER_EMAIL'] ?? null,
      'git_committer_name' => $env['CI_COMMITTER_NAME'] ?? null,
      'git_message' => $env['CI_COMMIT_MESSAGE'] ?? null,
      'service_job_id' => $env['CI_BUILD_NUMBER'] ?? null,
      'service_name' => 'codeship'
    ]);
  }
}
