<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\Configuration;

/** Fetches the [GitHub](https://github.com) configuration parameters from the environment. */
abstract class GitHub {

	/** Reads the configuration parameters from the specified array of environment variables. */
	static function getConfiguration(array $env): Configuration {
		$commitSha = $env["GITHUB_SHA"] ?? "";
		$repository = $env["GITHUB_REPOSITORY"] ?? "";

		$gitRef = $env["GITHUB_REF"] ?? "";
		$gitRegex = '#^refs/\w+/#';

		return new Configuration([
			"commit_sha" => $commitSha ?: null,
			"service_branch" => preg_match($gitRegex, $gitRef) ? preg_replace($gitRegex, "", $gitRef) : null,
			"service_build_url" => $commitSha && $repository ? "https://github.com/$repository/commit/$commitSha/checks" : null,
			"service_name" => "github"
		]);
	}
}

/* TODO: integrate the new changes!
abstract class GitHub {

	const SERVICE_BUILD_URL_TEMPLATE = "https://github.com/%s/commit/%s/checks";

	static function getConfiguration(array $env): Configuration {
		$commitSha = $env["GITHUB_SHA"] ?? "";
		$repository = $env["GITHUB_REPOSITORY"] ?? "";
		$jobId = $commitSha;
		$gitRef = $env["GITHUB_REF"] ?? "";
		$gitRegex = '#^refs/\w+/#';
		$eventName = $env["GITHUB_EVENT_NAME"];

		if ($eventName === "pull_request") {
			$event = static::getEvent($env["GITHUB_EVENT_PATH"]);
			$prNumber = (string)$event["number"];
			$jobId = sprintf("%s-PR-%s", $commitSha, $prNumber);
		}

		return new Configuration([
			"commit_sha" => $commitSha ?? null,
			"service_branch" => preg_match($gitRegex, $gitRef) ? preg_replace($gitRegex, "", $gitRef) : null,
			"service_build_url" => $commitSha && $repository ? sprintf(static::SERVICE_BUILD_URL_TEMPLATE, $repository, $commitSha) : null,
			"service_name" => "github",
			"service_job_id" => $jobId,
			"service_pull_request" => $prNumber ?? null,
		]);
	}

	static function getEvent(string $path): array {
		$data = file_get_contents($path);

		if ($data) {
			return json_decode($data, true) ?: [];
		}

		return [];
	}
}*/
