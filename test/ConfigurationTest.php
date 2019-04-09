<?php declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\Configuration` class.
 */
class ConfigurationTest extends TestCase {

  /**
   * Tests the `ArrayAccess` interface.
   * @test
   */
  function testArrayAccess(): void {
    $config = new Configuration;

    // It should handle the existence of an element.
    assertThat($config->offsetExists('foo'), isFalse());
    $config->offsetSet('foo', 'bar');
    assertThat($config->offsetExists('foo'), isTrue());
    $config->offsetUnset('foo');

    // It should handle the fetch of an element.
    assertThat($config->offsetGet('foo'), isNull());
    $config->offsetSet('foo', 'bar');
    assertThat($config->offsetGet('foo'), equalTo('bar'));
    $config->offsetUnset('foo');
  }

  /**
   * Tests the `Configuration::count()` method.
   * @test
   */
  function testCount(): void {
    // It should return zero for an empty configuration.
    assertThat(new Configuration, isEmpty());

    // It should return the number of entries for a non-empty configuration.
    assertThat(new Configuration(['foo' => 'bar', 'bar' => 'baz']), countOf(2));
  }

  /**
   * Tests the `Configuration::fromEnvironment()` method.
   * @test
   */
  function testFromEnvironment(): void {
    // It should return an empty configuration for an empty environment.
    $config = Configuration::fromEnvironment([]);
    assertThat($config, isEmpty());

    // It should return an initialized instance for a non-empty environment.
    $config = Configuration::fromEnvironment([
      'CI_NAME' => 'travis-pro',
      'CI_PULL_REQUEST' => 'PR #123',
      'COVERALLS_REPO_TOKEN' => '0123456789abcdef',
      'GIT_MESSAGE' => 'Hello World!',
      'TRAVIS' => 'true',
      'TRAVIS_BRANCH' => 'develop'
    ]);

    assertThat($config['commit_sha'], equalTo('HEAD'));
    assertThat($config['git_message'], equalTo('Hello World!'));
    assertThat($config['repo_token'], equalTo('0123456789abcdef'));
    assertThat($config['service_branch'], equalTo('develop'));
    assertThat($config['service_name'], equalTo('travis-pro'));
    assertThat($config['service_pull_request'], equalTo('123'));
  }

  /**
   * Tests the `Configuration::fromYaml()` method.
   * @test
   */
  function testFromYaml(): void {
    // It should return an initialized instance for a non-empty map.
    $config = Configuration::fromYaml("repo_token: 0123456789abcdef\nservice_name: travis-ci");
    assertThat($config, countOf(2));
    assertThat($config['repo_token'], equalTo('0123456789abcdef'));
    assertThat($config['service_name'], equalTo('travis-ci'));

    // It should throw an exception with a non-object value.
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The specified YAML document is invalid.');
    Configuration::fromYaml('foo');
  }

  /**
   * Tests the `Configuration::getIterator()` method.
   * @test
   */
  function testGetIterator(): void {
    // It should return a done iterator if configuration is empty.
    $iterator = (new Configuration)->getIterator();
    assertThat($iterator->valid(), isFalse());

    // It should return a value iterator if configuration is not empty.
    $iterator = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getIterator();
    assertThat($iterator->valid(), isTrue());

    assertThat($iterator->key(), equalTo('foo'));
    assertThat($iterator->current(), equalTo('bar'));
    $iterator->next();

    assertThat($iterator->key(), equalTo('bar'));
    assertThat($iterator->current(), equalTo('baz'));
    $iterator->next();

    assertThat($iterator->valid(), isFalse());
  }

  /**
   * Tests the `Configuration::getKeys()` method.
   * @test
   */
  function testGetKeys(): void {
    // It should return an empty array for an empty configuration.
    assertThat((new Configuration)->getKeys(), isEmpty());

    // It should return the list of keys for a non-empty configuration.
    $keys = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getKeys();
    assertThat($keys, countOf(2));
    assertThat($keys[0], equalTo('foo'));
    assertThat($keys[1], equalTo('bar'));
  }

  /**
   * Tests the `Configuration::jsonSerialize()` method.
   * @test
   */
  function testJsonSerialize(): void {
    // It should return an empty map for a newly created instance.
    $map = (new Configuration)->jsonSerialize();
    assertThat(get_object_vars($map), isEmpty());

    // It should return a non-empty map for an initialized instance.
    $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(2));
    assertThat($map->foo, equalTo('bar'));
    assertThat($map->bar, equalTo('baz'));
  }

  /**
   * Tests the `Configuration::loadDefaults()` method.
   * @test
   */
  function testLoadDefaults(): void {
    // It should properly initialize from a `.coveralls.yml` file.
    $config = Configuration::loadDefaults('test/fixtures/.coveralls.yml');
    assertThat(count($config), greaterThanOrEqual(2));
    assertThat($config['repo_token'], equalTo('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt'));
    assertThat($config['service_name'], equalTo('travis-pro'));

    // It should use the environment defaults if the `.coveralls.yml` file is not found.
    $defaults = Configuration::fromEnvironment();
    $config = Configuration::loadDefaults('.dummy/config.yml');
    assertThat(get_object_vars($config->jsonSerialize()), equalTo(get_object_vars($defaults->jsonSerialize())));
  }

  /**
   * Tests the `Configuration::merge()` method.
   * @test
   */
  function testMerge(): void {
    // It should have the same entries as the other configuration.
    $config = new Configuration;
    assertThat($config, isEmpty());

    $config->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
    assertThat($config, countOf(2));
    assertThat($config['foo'], equalTo('bar'));
    assertThat($config['bar'], equalTo('baz'));
  }
}
