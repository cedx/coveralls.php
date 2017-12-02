<?php
declare(strict_types=1);
namespace Coveralls;

/**
 * Represents Git data that can be used to display more information to users.
 */
class GitData implements \JsonSerializable {

  /**
   * @var string The branch name.
   */
  private $branch;

  /**
   * @var GitCommit The Git commit.
   */
  private $commit;

  /**
   * @var \ArrayObject The remote repositories.
   */
  private $remotes;

  /**
   * Initializes a new instance of the class.
   * @param GitCommit $commit The Git commit.
   * @param string $branch The branch name.
   * @param GitRemote[] $remotes The remote repositories.
   */
  public function __construct(GitCommit $commit, string $branch = '', array $remotes = []) {
    $this->commit = $commit;
    $this->setBranch($branch);
    $this->remotes = new \ArrayObject($remotes);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Creates a new Git data from the specified JSON map.
   * @param mixed $map A JSON map representing a Git data.
   * @return GitData The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJson($map): ?self {
    $transform = function($remotes) {
      return array_values(array_filter(array_map([GitRemote::class, 'fromJson'], $remotes)));
    };

    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : new static(
      GitCommit::fromJson($map->head ?? []),
      isset($map->branch) && is_string($map->branch) ? $map->branch : '',
      isset($map->remotes) && is_array($map->remotes) ? $transform($map->remotes) : []
    );
  }

  /**
   * Creates a new Git data from a local repository.
   * This method relies on the availability of the Git executable in the system path.
   * @param string $path The path to the repository folder. Defaults to the current working directory.
   * @return GitData The newly created Git data.
   */
  public static function fromRepository(string $path = ''): self {
    if (!mb_strlen($path)) $path = getcwd();

    $workingDir = getcwd();
    chdir($path);

    $commands = array_map(function($command) { return trim(`git $command`); }, [
      'author_email' => 'log -1 --pretty=format:%ae',
      'author_name' => 'log -1 --pretty=format:%aN',
      'branch' => 'rev-parse --abbrev-ref HEAD',
      'committer_email' => 'log -1 --pretty=format:%ce',
      'committer_name' => 'log -1 --pretty=format:%cN',
      'id' => 'log -1 --pretty=format:%H',
      'message' => 'log -1 --pretty=format:%s',
      'remotes' => 'remote -v'
    ]);

    $remotes = [];
    foreach (preg_split('/\r?\n/', $commands['remotes']) as $remote) {
      $parts = explode(' ', preg_replace('/\s+/', ' ', $remote));
      if (!isset($remotes[$parts[0]])) $remotes[$parts[0]] = new GitRemote($parts[0], count($parts) > 1 ? $parts[1] : null);
    }

    chdir($workingDir);
    return new static(GitCommit::fromJson($commands), $commands['branch'], array_values($remotes));
  }

  /**
   * Gets the branch name.
   * @return string The branch name.
   */
  public function getBranch(): string {
    return $this->branch;
  }

  /**
   * Gets the Git commit.
   * @return GitCommit The Git commit.
   */
  public function getCommit(): ?GitCommit {
    return $this->commit;
  }

  /**
   * Gets the remote repositories.
   * @return \ArrayObject The remote repositories.
   */
  public function getRemotes(): \ArrayObject {
    return $this->remotes;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      'branch' => $this->getBranch(),
      'head' => ($commit = $this->getCommit()) ? $commit->jsonSerialize() : null,
      'remotes' => array_map(function(GitRemote $item): \stdClass {
        return $item->jsonSerialize();
      }, $this->getRemotes()->getArrayCopy())
    ];
  }

  /**
   * Sets the branch name.
   * @param string $value The new name.
   * @return GitData This instance.
   */
  public function setBranch(string $value): self {
    $this->branch = $value;
    return $this;
  }
}
