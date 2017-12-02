<?php
declare(strict_types=1);
namespace Coveralls;

use Evenement\{EventEmitterTrait};
use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{MultipartStream, Request, Uri};
use Psr\Http\Message\{UriInterface};
use function Which\{which};

/**
 * Uploads code coverage reports to the [Coveralls](https://coveralls.io) service.
 */
class Client {
  use EventEmitterTrait;

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://coveralls.io';

  /**
   * @var Uri The URL of the API end point.
   */
  private $endPoint;

  /**
   * Initializes a new instance of the class.
   * @param string|UriInterface $endPoint The URL of the API end point.
   */
  public function __construct($endPoint = self::DEFAULT_ENDPOINT) {
    $this->endPoint = is_string($endPoint) ? new Uri($endPoint) : $endPoint;
  }

  /**
   * Gets the URL of the API end point.
   * @return UriInterface The URL of the API end point.
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * Uploads the specified code coverage report to the Coveralls service.
   * @param string $coverage A coverage report.
   * @param Configuration $configuration The environment settings.
   * @throws \InvalidArgumentException The specified coverage report is empty or its format is not supported.
   */
  public function upload(string $coverage, Configuration $configuration = null): void {
    $report = trim($coverage);
    if (!mb_strlen($report)) throw new \InvalidArgumentException('The specified coverage report is empty.');

    /** @var Job $job */
    $job = null;
    $isClover = mb_substr($report, 0, 5) == '<?xml' || mb_substr($report, 0, 10) == '<coverage';
    if ($isClover) {
      require_once __DIR__.'/Parsers/Clover.php';
      $job = call_user_func('Coveralls\Parsers\Clover\parseReport', $report);
    }
    else {
      $token = mb_substr($report, 0, 3);
      if ($token == 'TN:' || $token == 'SF:') {
        require_once __DIR__.'/Parsers/Lcov.php';
        $job = call_user_func('Coveralls\Parsers\Lcov\parseReport', $report);
      }
    }

    if (!$job) throw new \InvalidArgumentException('The specified coverage format is not supported.');
    $this->updateJob($job, $configuration ?: Configuration::loadDefaults());
    if (!$job->getRunAt()) $job->setRunAt(time());

    try {
      if (mb_strlen(which('git'))) {
        $git = GitData::fromRepository();
        $branch = ($gitData = $job->getGit()) ? $gitData->getBranch() : '';
        if ($git->getBranch() == 'HEAD' && mb_strlen($branch)) $git->setBranch($branch);
        $job->setGit($git);
      }
    }

    catch (\RuntimeException $e) {}
    $this->uploadJob($job);
  }

  /**
   * Uploads the specified job to the Coveralls service.
   * @param Job $job The job to be uploaded.
   * @throws \InvalidArgumentException The job does not meet the requirements.
   * @throws \RuntimeException An error occurred while uploading the report.
   */
  public function uploadJob(Job $job): void {
    if (!$job->getRepoToken() && !$job->getServiceName())
      throw new \InvalidArgumentException('The job does not meet the requirements.');

    try {
      $body = new MultipartStream([[
        'contents' => json_encode($job, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'filename' => 'coveralls.json',
        'name' => 'json_file'
      ]]);

      $headers = [
        'Content-Length' => $body->getSize(),
        'Content-Type' => "multipart/form-data; boundary={$body->getBoundary()}"
      ];

      $request = new Request('POST', $this->getEndPoint()->withPath('/api/v1/jobs'), $headers, $body);
      $this->emit('request', [$request]);

      $response = (new HTTPClient())->send($request);
      $this->emit('reponse', [$response]);
    }

    catch (\Throwable $e) {
      throw new \RuntimeException('An error occurred while uploading the report.', 0, $e);
    }
  }

  /**
   * Updates the properties of the specified job using the given configuration parameters.
   * @param Job $job The job to update.
   * @param Configuration $config The parameters to define.
   */
  private function updateJob(Job $job, Configuration $config): void {
    if (isset($config['repo_token']) || isset($config['repo_secret_token']))
      $job->setRepoToken($config['repo_token'] ?? $config['repo_secret_token']);

    if (isset($config['parallel'])) $job->setParallel($config['parallel'] == 'true');
    if (isset($config['run_at'])) $job->setRunAt($config['run_at']);
    if (isset($config['service_job_id'])) $job->setServiceJobId($config['service_job_id']);
    if (isset($config['service_name'])) $job->setServiceName($config['service_name']);
    if (isset($config['service_number'])) $job->setServiceNumber($config['service_number']);
    if (isset($config['service_pull_request'])) $job->setServicePullRequest($config['service_pull_request']);

    $hasGitData = count(array_filter($config->getKeys(), function($key) {
      return $key == 'service_branch' || mb_substr($key, 0, 4) == 'git_';
    })) > 0;

    if (!$hasGitData) $job->setCommitSha($config['commit_sha'] ?: '');
    else {
      $commit = new GitCommit($config['commit_sha'] ?: '', $config['git_message'] ?: '');
      $commit->setAuthorEmail($config['git_author_email'] ?: '');
      $commit->setAuthorName($config['git_author_name'] ?: '');
      $commit->setCommitterEmail($config['git_committer_email'] ?: '');
      $commit->setCommitterName($config['git_committer_email'] ?: '');

      $job->setGit(new GitData($commit, $config['service_branch'] ?: ''));
    }
  }
}
