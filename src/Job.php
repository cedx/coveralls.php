<?php declare(strict_types=1);
namespace Coveralls;

/** Represents the coverage data from a single run of a test suite. */
class Job implements \JsonSerializable {

	/** The current SHA of the commit being built to override the `git` parameter. */
	private string $commitSha = "";

	/** The job name. */
	private string $flagName = "";

	/** The Git data that can be used to display more information to users. */
	private ?GitData $git = null;

	/** Value indicating whether the build will not be considered done until a webhook has been sent to Coveralls. */
	private bool $isParallel = false;

	/** The secret token for the repository. */
	private string $repoToken = "";

	/** The timestamp of when the job ran. */
	private ?\DateTimeImmutable $runAt = null;

	/** The unique identifier of the job on the CI service. */
	private string $serviceJobId = "";

	/** The CI service or other environment in which the test suite was run. */
	private string $serviceName = "";

	/** The build number. */
	private string $serviceNumber = "";

	/** The associated pull request identifier of the build. */
	private string $servicePullRequest = "";

	/** The list of source files. */
	private \ArrayObject $sourceFiles;

	/** Creates a new job. */
	function __construct(array $sourceFiles = []) {
		$this->sourceFiles = new \ArrayObject($sourceFiles);
	}

	/** Creates a new job from the specified JSON object. */
	static function fromJson(object $map): self {
		return (new self(isset($map->source_files) && is_array($map->source_files) ? array_map([SourceFile::class, "fromJson"], $map->source_files) : []))
			->setCommitSha(isset($map->commit_sha) && is_string($map->commit_sha) ? $map->commit_sha : "")
			->setFlagName(isset($map->flag_name) && is_string($map->flag_name) ? $map->flag_name : "")
			->setGit(isset($map->git) && is_object($map->git) ? GitData::fromJson($map->git) : null)
			->setParallel(isset($map->parallel) && is_bool($map->parallel) ? $map->parallel : false)
			->setRepoToken(isset($map->repo_token) && is_string($map->repo_token) ? $map->repo_token : "")
			->setRunAt(isset($map->run_at) && is_string($map->run_at) ? new \DateTimeImmutable($map->run_at) : null)
			->setServiceJobId(isset($map->service_job_id) && is_string($map->service_job_id) ? $map->service_job_id : "")
			->setServiceName(isset($map->service_name) && is_string($map->service_name) ? $map->service_name : "")
			->setServiceNumber(isset($map->service_number) && is_string($map->service_number) ? $map->service_number : "")
			->setServicePullRequest(isset($map->service_pull_request) && is_string($map->service_pull_request) ? $map->service_pull_request : "");
	}

	/** Gets the current SHA of the commit being built to override the `git` parameter. */
	function getCommitSha(): string {
		return $this->commitSha;
	}

	/** Gets the job name. */
	function getFlagName(): string {
		return $this->flagName;
	}

	/** Get the Git data that can be used to display more information to users. */
	function getGit(): ?GitData {
		return $this->git;
	}

	/** Gets the secret token for the repository. */
	function getRepoToken(): string {
		return $this->repoToken;
	}

	/** Gets the timestamp of when the job ran. */
	function getRunAt(): ?\DateTimeImmutable {
		return $this->runAt;
	}

	/** Gets the unique identifier of the job on the CI service. */
	function getServiceJobId(): string {
		return $this->serviceJobId;
	}

	/** Gets the CI service or other environment in which the test suite was run. */
	function getServiceName(): string {
		return $this->serviceName;
	}

	/** Gets the build number. */
	function getServiceNumber(): string {
		return $this->serviceNumber;
	}

	/** Gets the associated pull request identifier of the build. */
	function getServicePullRequest(): string {
		return $this->servicePullRequest;
	}

	/** Gets the list of source files. */
	function getSourceFiles(): \ArrayObject {
		return $this->sourceFiles;
	}

	/** Gets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls. */
	function isParallel(): bool {
		return $this->isParallel;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;

		if (mb_strlen($commitSha = $this->getCommitSha())) $map->commit_sha = $commitSha;
		if (mb_strlen($flagName = $this->getFlagName())) $map->flag_name = $flagName;
		if ($git = $this->getGit()) $map->git = $git->jsonSerialize();
		if ($this->isParallel()) $map->parallel = true;
		if (mb_strlen($repoToken = $this->getRepoToken())) $map->repo_token = $repoToken;
		if ($runAt = $this->getRunAt()) $map->run_at = $runAt->format("c");
		if (mb_strlen($serviceName = $this->getServiceName())) $map->service_name = $serviceName;
		if (mb_strlen($serviceNumber = $this->getServiceNumber())) $map->service_number = $serviceNumber;
		if (mb_strlen($serviceJobId = $this->getServiceJobId())) $map->service_job_id = $serviceJobId;
		if (mb_strlen($servicePullRequest = $this->getServicePullRequest())) $map->service_pull_request = $servicePullRequest;

		$map->source_files = array_map(fn(SourceFile $item) => $item->jsonSerialize(), (array) $this->getSourceFiles());
		return $map;
	}

	/** Sets the current SHA of the commit being built to override the `git` parameter. */
	function setCommitSha(string $value): static {
		$this->commitSha = $value;
		return $this;
	}

	/** Sets the job name. */
	function setFlagName(string $value): static {
		$this->flagName = $value;
		return $this;
	}

	/** Sets the Git data that can be used to display more information to users. */
	function setGit(?GitData $value): static {
		$this->git = $value;
		return $this;
	}

	/** Sets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls. */
	function setParallel(bool $value): static {
		$this->isParallel = $value;
		return $this;
	}

	/** Sets the secret token for the repository. */
	function setRepoToken(string $value): static {
		$this->repoToken = $value;
		return $this;
	}

	/** Sets the timestamp of when the job ran. */
	function setRunAt(?\DateTimeInterface $value): static {
		$this->runAt = $value ? \DateTimeImmutable::createFromInterface($value) : null;
		return $this;
	}

	/** Sets the unique identifier of the job on the CI service. */
	function setServiceJobId(string $value): static {
		$this->serviceJobId = $value;
		return $this;
	}

	/** Sets the CI service or other environment in which the test suite was run. */
	function setServiceName(string $value): static {
		$this->serviceName = $value;
		return $this;
	}

	/** Sets the build number. */
	function setServiceNumber(string $value): static {
		$this->serviceNumber = $value;
		return $this;
	}

	/** Sets the associated pull request identifier of the build. */
	function setServicePullRequest(string $value): static {
		$this->servicePullRequest = $value;
		return $this;
	}
}
