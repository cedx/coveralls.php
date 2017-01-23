<?php
/**
 * Provides a connector for the [Travis CI](https://travis-ci.com) service.
 */
namespace coveralls\services\travis_ci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'git_branch' => getenv('TRAVIS_BRANCH'),
    'git_commit' => 'HEAD',
    'service_job_id' => getenv('TRAVIS_JOB_ID'),
    'service_name' => 'travis-ci'
  ]);
}