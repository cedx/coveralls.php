<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Solano CI](https://ci.solanolabs.com) configuration parameters from the environment. */
abstract class SolanoCI {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    $serviceNumber = $env['TDDIUM_SESSION_ID'] ?? null;
    return new Configuration([
      'service_branch' => $env['TDDIUM_CURRENT_BRANCH'] ?? null,
      'service_build_url' => $serviceNumber ? "https://ci.solanolabs.com/reports/$serviceNumber" : null,
      'service_job_number' => $env['TDDIUM_TID'] ?? null,
      'service_name' => 'tddium',
      'service_number' => $serviceNumber,
      'service_pull_request' => $env['TDDIUM_PR_ID'] ?? null
    ]);
  }
}
