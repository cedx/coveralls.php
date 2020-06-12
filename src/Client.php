<?php declare(strict_types=1);
namespace Coveralls;

use Coveralls\Parsers\{Clover, Lcov};
use Psr\Http\Message\{UriInterface};
use Symfony\Component\EventDispatcher\{EventDispatcher};
use Symfony\Component\HttpClient\{Psr18Client};
use Symfony\Component\Mime\Part\{DataPart};
use Symfony\Component\Mime\Part\Multipart\{FormDataPart};
use Which\{FinderException};
use function Which\{which};

/** Uploads code coverage reports to the [Coveralls](https://coveralls.io) service. */
class Client extends EventDispatcher {

	/** @var string The URL of the default API end point. */
	const defaultEndPoint = "https://coveralls.io/api/v1/";

	/** @var string An event that is triggered when a request is made to the remote service. */
	const eventRequest = RequestEvent::class;

	/** @var string An event that is triggered when a response is received from the remote service. */
	const eventResponse = ResponseEvent::class;

	/** @var UriInterface The URL of the API end point. */
	private UriInterface $endPoint;

	/** @var Psr18Client The HTTP client. */
	private Psr18Client $http;

	/**
	 * Creates a new client.
	 * @param UriInterface|null $endPoint The URL of the API end point.
	 */
	function __construct(?UriInterface $endPoint = null) {
		parent::__construct();
		$this->http = new Psr18Client;
		$this->endPoint = $endPoint ?? $this->http->createUri(static::defaultEndPoint);
	}

	/**
	 * Gets the URL of the API end point.
	 * @return UriInterface The URL of the API end point.
	 */
	function getEndPoint(): UriInterface {
		return $this->endPoint;
	}

	/**
	 * Uploads the specified code coverage report to the Coveralls service.
	 * @param string $coverage A coverage report.
	 * @param Configuration $config The environment settings.
	 * @throws \InvalidArgumentException The format of the specified coverage report is not supported.
	 */
	function upload(string $coverage, Configuration $config = null): void {
		assert(mb_strlen($coverage) > 0);

		$job = null;
		$report = trim($coverage);
		if (mb_substr($report, 0, 5) == "<?xml" || mb_substr($report, 0, 9) == "<coverage")
			$job = Clover::parseReport($report);
		else {
			$token = mb_substr($report, 0, 3);
			if ($token == "TN:" || $token == "SF:") $job = Lcov::parseReport($report);
		}

		if (!$job) throw new \InvalidArgumentException("The specified coverage format is not supported.");
		$this->updateJob($job, $config ?? Configuration::loadDefaults());
		if (!$job->getRunAt()) $job->setRunAt(new \DateTimeImmutable);

		try {
			which("git");
			$git = GitData::fromRepository();
			$branch = ($gitData = $job->getGit()) ? $gitData->getBranch() : "";
			if ($git->getBranch() == "HEAD" && mb_strlen($branch)) $git->setBranch($branch);
			$job->setGit($git);
		}

		catch (FinderException $e) {}
		$this->uploadJob($job);
	}

	/**
	 * Uploads the specified job to the Coveralls service.
	 * @param Job $job The job to be uploaded.
	 * @throws \InvalidArgumentException The job does not meet the requirements.
	 * @throws ClientException An error occurred while uploading the report.
	 */
	function uploadJob(Job $job): void {
		if (!$job->getRepoToken() && !$job->getServiceName())
			throw new \InvalidArgumentException("The job does not meet the requirements.");

		$endPoint = $this->getEndPoint();
		$uri = $endPoint->withPath("{$endPoint->getPath()}jobs");

		try {
			$jsonFile = json_encode($job, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$formData = new FormDataPart(["json_file" => new DataPart($jsonFile, "coveralls.json")]);

			$request = ($this->http->createRequest("POST", $uri))->withBody($this->http->createStream($formData->bodyToString()));
			foreach ($formData->getPreparedHeaders()->all() as $header) {
				/** @var \Symfony\Component\Mime\Header\HeaderInterface $header */
				$request = $request->withHeader($header->getName(), $header->getBodyAsString());
			}

			$this->dispatch(new RequestEvent($request));
			$response = $this->http->sendRequest($request);
			$this->dispatch(new ResponseEvent($response, $request));
		}

		catch (\Throwable $e) {
			throw new ClientException($e->getMessage(), $uri, $e);
		}
	}

	/**
	 * Updates the properties of the specified job using the given configuration parameters.
	 * @param Job $job The job to update.
	 * @param Configuration $config The parameters to define.
	 */
	private function updateJob(Job $job, Configuration $config): void {
		if (isset($config["repo_token"])) $job->setRepoToken($config["repo_token"]);
		else if (isset($config["repo_secret_token"])) $job->setRepoToken($config["repo_secret_token"]);

		if (isset($config["parallel"])) $job->setParallel($config["parallel"] == "true");
		if (isset($config["flag_name"])) $job->setFlagName($config["flag_name"]);
		if (isset($config["run_at"])) $job->setRunAt(new \DateTimeImmutable($config["run_at"]));
		if (isset($config["service_job_id"])) $job->setServiceJobId($config["service_job_id"]);
		if (isset($config["service_name"])) $job->setServiceName($config["service_name"]);
		if (isset($config["service_number"])) $job->setServiceNumber($config["service_number"]);
		if (isset($config["service_pull_request"])) $job->setServicePullRequest($config["service_pull_request"]);

		$hasGitData = count(array_filter($config->getKeys(), fn($key) => $key == "service_branch" || mb_substr($key, 0, 4) == "git_")) > 0;
		if (!$hasGitData) $job->setCommitSha($config["commit_sha"] ?: "");
		else {
			$commit = new GitCommit($config["commit_sha"] ?: "", $config["git_message"] ?: "");
			$commit->setAuthorEmail($config["git_author_email"] ?: "");
			$commit->setAuthorName($config["git_author_name"] ?: "");
			$commit->setCommitterEmail($config["git_committer_email"] ?: "");
			$commit->setCommitterName($config["git_committer_email"] ?: "");

			$job->setGit(new GitData($commit, $config["service_branch"] ?: ""));
		}
	}
}
