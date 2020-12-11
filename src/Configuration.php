<?php declare(strict_types=1);
namespace Coveralls;

use Coveralls\Services\{AppVeyor, CircleCI, Codeship, GitHub, GitLabCI, Jenkins, Semaphore, SolanoCI, Surf, TravisCI, Wercker};
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/** Provides access to the coverage settings. */
class Configuration implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

	/** Creates a new configuration. */
	function __construct(private array $params = []) {}

	/** Creates a new configuration from the specified array of environment variables. */
	static function fromEnvironment(?array $env = null): self {
		$config = new self;
		$env ??= $_SERVER;

		// Standard.
		$serviceName = $env["CI_NAME"] ?? "";
		if (mb_strlen($serviceName)) $config["service_name"] = $serviceName;

		if (isset($env["CI_BRANCH"])) $config["service_branch"] = $env["CI_BRANCH"];
		if (isset($env["CI_BUILD_NUMBER"])) $config["service_number"] = $env["CI_BUILD_NUMBER"];
		if (isset($env["CI_BUILD_URL"])) $config["service_build_url"] = $env["CI_BUILD_URL"];
		if (isset($env["CI_COMMIT"])) $config["commit_sha"] = $env["CI_COMMIT"];
		if (isset($env["CI_JOB_ID"])) $config["service_job_id"] = $env["CI_JOB_ID"];

		if (isset($env["CI_PULL_REQUEST"]) && preg_match('/(\d+)$/', $env["CI_PULL_REQUEST"], $matches)) {
			if (count($matches) >= 2) $config["service_pull_request"] = $matches[1];
		}

		// Coveralls.
		if (isset($env["COVERALLS_REPO_TOKEN"]) || isset($env["COVERALLS_TOKEN"]))
			$config["repo_token"] = $env["COVERALLS_REPO_TOKEN"] ?? $env["COVERALLS_TOKEN"];

		if (isset($env["COVERALLS_COMMIT_SHA"])) $config["commit_sha"] = $env["COVERALLS_COMMIT_SHA"];
		if (isset($env["COVERALLS_FLAG_NAME"])) $config["flag_name"] = $env["COVERALLS_FLAG_NAME"];
		if (isset($env["COVERALLS_PARALLEL"])) $config["parallel"] = $env["COVERALLS_PARALLEL"];
		if (isset($env["COVERALLS_RUN_AT"])) $config["run_at"] = $env["COVERALLS_RUN_AT"];
		if (isset($env["COVERALLS_SERVICE_BRANCH"])) $config["service_branch"] = $env["COVERALLS_SERVICE_BRANCH"];
		if (isset($env["COVERALLS_SERVICE_JOB_ID"])) $config["service_job_id"] = $env["COVERALLS_SERVICE_JOB_ID"];
		if (isset($env["COVERALLS_SERVICE_NAME"])) $config["service_name"] = $env["COVERALLS_SERVICE_NAME"];

		// Git.
		if (isset($env["GIT_AUTHOR_EMAIL"])) $config["git_author_email"] = $env["GIT_AUTHOR_EMAIL"];
		if (isset($env["GIT_AUTHOR_NAME"])) $config["git_author_name"] = $env["GIT_AUTHOR_NAME"];
		if (isset($env["GIT_BRANCH"])) $config["service_branch"] = $env["GIT_BRANCH"];
		if (isset($env["GIT_COMMITTER_EMAIL"])) $config["git_committer_email"] = $env["GIT_COMMITTER_EMAIL"];
		if (isset($env["GIT_COMMITTER_NAME"])) $config["git_committer_name"] = $env["GIT_COMMITTER_NAME"];
		if (isset($env["GIT_ID"])) $config["commit_sha"] = $env["GIT_ID"];
		if (isset($env["GIT_MESSAGE"])) $config["git_message"] = $env["GIT_MESSAGE"];

		// CI services.
		if (isset($env["TRAVIS"])) {
			$config->merge(TravisCI::getConfiguration($env));
			if (mb_strlen($serviceName) && $serviceName != "travis-ci") $config["service_name"] = $serviceName;
		}
		else if (isset($env["APPVEYOR"])) $config->merge(AppVeyor::getConfiguration($env));
		else if (isset($env["CIRCLECI"])) $config->merge(CircleCI::getConfiguration($env));
		else if ($serviceName == "codeship") $config->merge(Codeship::getConfiguration($env));
		else if (isset($env["GITHUB_WORKFLOW"])) $config->merge(GitHub::getConfiguration($env));
		else if (isset($env["GITLAB_CI"])) $config->merge(GitLabCI::getConfiguration($env));
		else if (isset($env["JENKINS_URL"])) $config->merge(Jenkins::getConfiguration($env));
		else if (isset($env["SEMAPHORE"])) $config->merge(Semaphore::getConfiguration($env));
		else if (isset($env["SURF_SHA1"])) $config->merge(Surf::getConfiguration($env));
		else if (isset($env["TDDIUM"])) $config->merge(SolanoCI::getConfiguration($env));
		else if (isset($env["WERCKER"])) $config->merge(Wercker::getConfiguration($env));

		return $config;
	}

	/**
	 * Creates a new configuration from the specified YAML document.
	 * @throws \InvalidArgumentException The specified document is invalid.
	 */
	static function fromYaml(string $document): self {
		try {
			is_array($yaml = Yaml::parse($document)) || throw new \InvalidArgumentException("The specified YAML document is invalid.");
			return new self($yaml);
		}

		catch (ParseException $e) {
			throw new \InvalidArgumentException("The specified YAML document is invalid.", 0, $e);
		}
	}

	/**
	 * Loads the default configuration.
	 * The default values are read from the environment variables and an optional `.coveralls.yml` file.
	 */
	static function loadDefaults(string $coverallsFile = ".coveralls.yml"): self {
		$defaults = static::fromEnvironment();

		try {
			$file = new \SplFileObject($coverallsFile);
			if ($file->isReadable()) $defaults->merge(static::fromYaml((string) $file->fread((int) $file->getSize())));
			return $defaults;
		}

		catch (\Throwable $e) {
			return $defaults;
		}
	}

	/** Gets the number of entries in this configuration. */
	function count(): int {
		return count($this->params);
	}

	/** Returns a new iterator that allows iterating the elements of this configuration. */
	function getIterator(): \Traversable {
		return new \ArrayIterator($this->params);
	}

	/** Gets the keys of this configuration. */
	function getKeys(): array {
		return array_keys($this->params);
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		return (object) $this->params;
	}

	/** Adds all entries of the specified configuration to this one, ignoring `null` values. */
	function merge(self $config): void {
		foreach ($config as $key => $value)
			if ($value !== null) $this[$key] = $value;
	}

	/** Gets a value indicating whether this configuration contains the specified key. */
	function offsetExists($key): bool {
		return isset($this->params[$key]);
	}

	/** Gets the value associated to the specified key. */
	function offsetGet($key): ?string {
		return $this->params[$key] ?? null;
	}

	/** Associates a given value to the specified key. */
	function offsetSet($key, $value): void {
		$this->params[$key] = $value;
	}

	/** Removes the value associated to the specified key. */
	function offsetUnset($key): void {
		unset($this->params[$key]);
	}
}
