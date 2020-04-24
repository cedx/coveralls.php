<?php declare(strict_types=1);
namespace Coveralls;

/** Represents Git data that can be used to display more information to users. */
class GitData implements \JsonSerializable {

  /** @var string The branch name. */
  private string $branch;

  /** @var GitCommit|null The Git commit. */
  private ?GitCommit $commit;

  /** @var \ArrayObject<int, GitRemote> The remote repositories. */
  private \ArrayObject $remotes;

  /**
   * Creates a new Git data.
   * @param GitCommit $commit The Git commit.
   * @param string $branch The branch name.
   * @param GitRemote[] $remotes The remote repositories.
   */
  function __construct(?GitCommit $commit, string $branch = '', array $remotes = []) {
    $this->branch = $branch;
    $this->commit = $commit;
    $this->remotes = new \ArrayObject($remotes);
  }

  /**
   * Creates a new Git data from the specified JSON object.
   * @param object $map A JSON object representing a Git data.
   * @return self The instance corresponding to the specified JSON object.
   */
  static function fromJson(object $map): self {
    return new self(
      isset($map->head) && is_object($map->head) ? GitCommit::fromJson($map->head) : null,
      isset($map->branch) && is_string($map->branch) ? $map->branch : '',
      isset($map->remotes) && is_array($map->remotes) ? array_map([GitRemote::class, 'fromJson'], $map->remotes) : []
    );
  }

  /**
   * Creates a new Git data from a local repository.
   * This method relies on the availability of the Git executable in the system path.
   * @param string $path The path to the repository folder. Defaults to the current working directory.
   * @return self The newly created Git data.
   */
  static function fromRepository(string $path = ''): self {
    $workingDir = getcwd() ?: '.';
    if (!mb_strlen($path)) $path = $workingDir;
    chdir($path);

    $commands = (object) array_map(fn($command) => trim(`git $command`), [
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
    foreach (preg_split('/\r?\n/', $commands->remotes) ?: [] as $remote) {
      $parts = explode(' ', (string) preg_replace('/\s+/', ' ', $remote));
      $remotes[$parts[0]] ??= new GitRemote($parts[0], count($parts) > 1 ? $parts[1] : null);
    }

    chdir($workingDir);
    return new self(GitCommit::fromJson($commands), $commands->branch, array_values($remotes));
  }

  /**
   * Gets the branch name.
   * @return string The branch name.
   */
  function getBranch(): string {
    return $this->branch;
  }

  /**
   * Gets the Git commit.
   * @return GitCommit The Git commit.
   */
  function getCommit(): ?GitCommit {
    return $this->commit;
  }

  /**
   * Gets the remote repositories.
   * @return \ArrayObject<int, GitRemote> The remote repositories.
   */
  function getRemotes(): \ArrayObject {
    return $this->remotes;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    return (object) [
      'branch' => $this->getBranch(),
      'head' => ($commit = $this->getCommit()) ? $commit->jsonSerialize() : null,
      'remotes' => array_map(fn(GitRemote $item) => $item->jsonSerialize(), (array) $this->getRemotes())
    ];
  }

  /**
   * Sets the branch name.
   * @param string $value The new name.
   * @return $this This instance.
   */
  function setBranch(string $value): self {
    $this->branch = $value;
    return $this;
  }
}
