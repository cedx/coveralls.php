<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [GitHub](https://github.com) configuration parameters from the environment. */
abstract class GitHub {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    $commitSha = $environment['GITHUB_SHA'] ?? '';
    $repository = $environment['GITHUB_REPOSITORY'] ?? '';
    $jobId = $commitSha;
    $gitRef = $environment['GITHUB_REF'] ?? '';
    $gitRegex = '#^refs/\w+/#';
    $eventName = $environment['GITHUB_EVENT_NAME'];

    if ($eventName === 'pull_request') {
      $event = static::getEvent($environment['GITHUB_EVENT_PATH']);
      $prNumber = (string)$event['number'];
      $jobId = sprintf('%s-PR-%s', $commitSha, $prNumber);
    }

    return new Configuration([
      'commit_sha' => $commitSha ?? null,
      'service_branch' => preg_match($gitRegex, $gitRef) ? preg_replace($gitRegex, '', $gitRef) : null,
      'service_build_url' => $commitSha && $repository ? "https://github.com/$repository/commit/$commitSha/checks" : null,
      'service_name' => 'github',
      'service_job_id' => $jobId,
      'service_pull_request' => $prNumber ?? null,
    ]);
  }

  static function getEvent(string $path): array {
    $data = file_get_contents($path);

    if ($data) {
      return json_decode($data, true) ?: [];
    }

    return [];
  }
}
