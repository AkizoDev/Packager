<?php

/**
 * Default implementation of IGit interface
 *
 * @author  Jan Pecha, <janpecha@email.cz>
 * @license New BSD License (BSD-3), see file license.md
 */

class GitRepository {
    /** @var  string */
    protected $repository;

    /** @var  string|NULL  @internal */
    protected $cwd;


    /**
     * @param  string
     * @throws Exception
     */
    public function __construct($repository) {
        if (basename($repository) === '.git') {
            $repository = dirname($repository);
        }

        $this->repository = realpath($repository);

        if ($this->repository === false) {
            throw new \Exception("Repository '$repository' not found.");
        }
    }


    /**
     * @return string
     */
    public function getRepositoryPath() {
        return $this->repository;
    }


    /**
     * Creates a tag.
     * `git tag <name>`
     * @param  string
     * @throws \Exception
     * @return self
     */
    public function createTag($name) {
        return $this->begin()
            ->run('git tag', $name)
            ->end();
    }


    /**
     * Removes tag.
     * `git tag -d <name>`
     * @param  string
     * @throws \Exception
     * @return self
     */
    public function removeTag($name) {
        return $this->begin()
            ->run('git tag', [
                '-d' => $name,
            ])
            ->end();
    }


    /**
     * Renames tag.
     * `git tag <new> <old>`
     * `git tag -d <old>`
     * @param  string
     * @param  string
     * @throws \Exception
     * @return self
     */
    public function renameTag($oldName, $newName) {
        return $this->begin()
            // http://stackoverflow.com/a/1873932
            // create new as alias to old (`git tag NEW OLD`)
            ->run('git tag', $newName, $oldName)
            // delete old (`git tag -d OLD`)
            ->removeTag($oldName)// WARN! removeTag() calls end() method!!!
            ->end();
    }


    /**
     * Returns list of tags in repo.
     * @return string[]|NULL  NULL => no tags
     */
    public function getTags() {
        return $this->extractFromCommand('git tag', 'trim');
    }


    /**
     * Merges branches.
     * `git merge <options> <name>`
     * @param  string
     * @param  array|NULL
     * @throws \Exception
     * @return self
     */
    public function merge($branch, $options = null) {
        return $this->begin()
            ->run('git merge', $options, $branch)
            ->end();
    }


    /**
     * Creates new branch.
     * `git branch <name>`
     * (optionaly) `git checkout <name>`
     * @param  string
     * @param  bool
     * @throws \Exception
     * @return self
     */
    public function createBranch($name, $checkout = false) {
        $this->begin();

        // git branch $name
        $this->run('git branch', $name);

        if ($checkout) {
            $this->checkout($name);
        }

        return $this->end();
    }


    /**
     * Removes branch.
     * `git branch -d <name>`
     * @param  string
     * @throws \Exception
     * @return self
     */
    public function removeBranch($name) {
        return $this->begin()
            ->run('git branch', [
                '-d' => $name,
            ])
            ->end();
    }


    /**
     * Gets name of current branch
     * `git branch` + magic
     * @return string
     * @throws \Exception
     */
    public function getCurrentBranchName() {
        try {
            $branch = $this->extractFromCommand('git branch -a', function ($value) {
                if (isset($value[0]) && $value[0] === '*') {
                    return trim(substr($value, 1));
                }

                return false;
            });

            if (is_array($branch)) {
                return $branch[0];
            }
        } catch (\Exception $e) {
        }
        throw new \Exception('Getting current branch name failed.');
    }


    /**
     * Returns list of all (local & remote) branches in repo.
     * @return string[]|NULL  NULL => no branches
     */
    public function getBranches() {
        return $this->extractFromCommand('git branch -a', function ($value) {
            return trim(substr($value, 1));
        });
    }


    /**
     * Returns list of local branches in repo.
     * @return string[]|NULL  NULL => no branches
     */
    public function getLocalBranches() {
        return $this->extractFromCommand('git branch', function ($value) {
            return trim(substr($value, 1));
        });
    }


    /**
     * Checkout branch.
     * `git checkout <branch>`
     * @param  string
     * @throws \Exception
     * @return self
     */
    public function checkout($name) {
        return $this->begin()
            ->run('git checkout', $name)
            ->end();
    }


    /**
     * Removes file(s).
     * `git rm <file>`
     * @param  string|string[]
     * @throws \Exception
     * @return self
     */
    public function removeFile($file) {
        if (!is_array($file)) {
            $file = func_get_args();
        }

        $this->begin();

        foreach ($file as $item) {
            $this->run('git rm', $item, '-r');
        }

        return $this->end();
    }


    /**
     * Adds file(s).
     * `git add <file>`
     * @param  string|string[]
     * @throws \Exception
     * @return self
     */
    public function addFile($file) {
        if (!is_array($file)) {
            $file = func_get_args();
        }

        $this->begin();

        foreach ($file as $item) {
            // TODO: ?? is file($repo . / . $item) ??
            $this->run('git add', $item);
        }

        return $this->end();
    }


    /**
     * Adds all created, modified & removed files.
     * `git add --all`
     * @throws \Exception
     * @return self
     */
    public function addAllChanges() {
        return $this->begin()
            ->run('git add --all')
            ->end();
    }


    /**
     * Renames file(s).
     * `git mv <file>`
     * @param  string|string[] from : array('from' => 'to', ...) || (from, to)
     * @param  string|NULL
     * @throws \Exception
     * @return self
     */
    public function renameFile($file, $to = null) {
        if (!is_array($file)) // rename(file, to);
        {
            $file = [
                $file => $to,
            ];
        }

        $this->begin();

        foreach ($file as $from => $to) {
            $this->run('git mv', $from, $to);
        }

        return $this->end();
    }


    /**
     * Commits changes
     * `git commit <params> -m <message>`
     * @param           string
     * @param  string[] param => value
     * @throws \Exception
     * @return self
     */
    public function commit($message, $params = null) {
        if (!is_array($params)) {
            $params = [];
        }

        return $this->begin()
            ->run("git commit", $params, [
                '-m' => $message,
            ])
            ->end();
    }


    /**
     * Exists changes?
     * `git status` + magic
     * @return bool
     */
    public function hasChanges() {
        $this->begin();
        $lastLine = exec('git status');
        $this->end();
        return (strpos($lastLine, 'nothing to commit')) === false; // FALSE => changes
    }


    /**
     * @deprecated
     */
    public function isChanges() {
        return $this->hasChanges();
    }


    /**
     * Pull changes from a remote
     * @param  string|NULL
     * @param array|null $params
     * @return GitRepository
     */
    public function pull($remote = null, array $params = null) {
        if (!is_array($params)) {
            $params = [];
        }

        return $this->begin()
            ->run("git pull $remote", $params)
            ->end();
    }


    /**
     * Push changes to a remote
     * @param  string|NULL
     * @param array|null $params
     * @return GitRepository
     */
    public function push($remote = null, array $params = null) {
        if (!is_array($params)) {
            $params = [];
        }

        return $this->begin()
            ->run("git push $remote", $params)
            ->end();
    }


    /**
     * Run fetch command to get latest branches
     * @param  string|NULL
     * @param array|null $params
     * @return GitRepository
     */
    public function fetch($remote = null, array $params = null) {
        if (!is_array($params)) {
            $params = [];
        }

        return $this->begin()
            ->run("git fetch $remote", $params)
            ->end();
    }


    /**
     * Adds new remote repository
     * @param            $name
     * @param            $url
     * @param array|null $params
     * @return GitRepository
     * @internal param $string
     * @internal param $string
     * @internal param $ array|NULL
     */
    public function addRemote($name, $url, array $params = null) {
        return $this->begin()
            ->run('git remote add', $params, $name, $url)
            ->end();
    }


    /**
     * Renames remote repository
     * @param  string
     * @param  string
     * @return self
     */
    public function renameRemote($oldName, $newName) {
        return $this->begin()
            ->run('git remote rename', $oldName, $newName)
            ->end();
    }


    /**
     * Removes remote repository
     * @param  string
     * @return self
     */
    public function removeRemote($name) {
        return $this->begin()
            ->run('git remote remove', $name)
            ->end();
    }


    /**
     * Changes remote repository URL
     * @param            $name
     * @param            $url
     * @param array|null $params
     * @return GitRepository
     * @internal param $string
     * @internal param $string
     * @internal param $ array|NULL
     */
    public function setRemoteUrl($name, $url, array $params = null) {
        return $this->begin()
            ->run('git remote set-url', $params, $name, $url)
            ->end();
    }


    /**
     * @return self
     */
    protected function begin() {
        if ($this->cwd === null) // TODO: good idea??
        {
            $this->cwd = getcwd();
            chdir($this->repository);
        }

        return $this;
    }


    /**
     * @return self
     */
    protected function end() {
        if (is_string($this->cwd)) {
            chdir($this->cwd);
        }

        $this->cwd = null;
        return $this;
    }


    /**
     * @param      $cmd
     * @param null $filter
     * @return NULL|string[]
     * @throws Exception
     * @internal param $string
     * @internal param $ callback|NULL
     */
    protected function extractFromCommand($cmd, $filter = null) {
        $output = [];
        $exitCode = null;

        $this->begin();
        exec("$cmd", $output, $exitCode);
        $this->end();

        if ($exitCode !== 0 || !is_array($output)) {
            throw new \Exception("Command $cmd failed.");
        }

        if ($filter !== null) {
            $newArray = [];

            foreach ($output as $line) {
                /** @var callable $filter */
                $value = $filter($line);

                if ($value === false) {
                    continue;
                }

                $newArray[] = $value;
            }

            $output = $newArray;
        }

        if (!isset($output[0])) // empty array
        {
            return null;
        }

        return $output;
    }


    /**
     * Runs command.
     * @param  string|array
     * @return self
     * @throws \Exception
     */
    protected function run($cmd/*, $options = NULL*/) {
        $args = func_get_args();
        $cmd = self::processCommand($args);
        exec($cmd, $output, $ret);

        if ($ret !== 0) {
            throw new \Exception("Command '$cmd' failed.");
        }

        return $this;
    }


    protected static function processCommand(array $args) {
        $cmd = [];

        $programName = array_shift($args);

        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    $_c = '';

                    if (is_string($key)) {
                        $_c = "$key ";
                    }

                    $cmd[] = $_c . escapeshellarg($value);
                }
            } elseif (is_scalar($arg) && !is_bool($arg)) {
                $cmd[] = escapeshellarg($arg);
            }
        }

        return "$programName " . implode(' ', $cmd);
    }


    /**
     * Init repo in directory
     * @param            string
     * @param array|null $params
     * @return GitRepository
     * @throws Exception
     */
    public static function init($directory, array $params = null) {
        if (is_dir("$directory/.git")) {
            throw new \Exception("Repo already exists in $directory.");
        }

        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) // intentionally @; not atomic; from Nette FW
        {
            throw new \Exception("Unable to create directory '$directory'.");
        }

        $cwd = getcwd();
        chdir($directory);
        exec(self::processCommand([
            'git init',
            $params,
            $directory,
        ]), $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Git init failed (directory $directory).");
        }

        $repo = getcwd();
        chdir($cwd);

        return new static($repo);
    }


    /**
     * Clones GIT repository from $url into $directory
     * @param            $url
     * @param null       $directory
     * @param array|null $params
     * @return GitRepository
     * @throws Exception
     * @internal param $string
     * @internal param $ string|NULL
     * @internal param $ array|NULL
     */
    public static function cloneRepository($url, $directory = null, array $params = null) {
        if ($directory !== null && is_dir("$directory/.git")) {
            throw new \Exception("Repo already exists in $directory.");
        }

        $cwd = getcwd();

        if ($directory === null) {
            $directory = self::extractRepositoryNameFromUrl($url);
            $directory = "$cwd/$directory";
        } elseif (!self::isAbsolute($directory)) {
            $directory = "$cwd/$directory";
        }

        if ($params === null) {
            $params = '-q';
        }

        exec(self::processCommand([
            'git clone',
            $params,
            $url,
            $directory
        ]), $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Git clone failed (directory $directory).");
        }

        return new static($directory);
    }


    /**
     * @param            string
     * @param array|null $refs
     * @return bool
     */
    public static function isRemoteUrlReadable($url, array $refs = null) {
        exec(self::processCommand([
                'GIT_TERMINAL_PROMPT=0 git ls-remote',
                '--heads',
                '--quiet',
                '--exit-code',
                $url,
                $refs,
            ]) . ' 2>&1', $output, $returnCode);

        return $returnCode === 0;
    }


    /**
     * @param  string /path/to/repo.git | host.xz:foo/.git | ...
     * @return string  repo | foo | ...
     */
    public static function extractRepositoryNameFromUrl($url) {
        // /path/to/repo.git => repo
        // host.xz:foo/.git => foo
        $directory = rtrim($url, '/');
        if (substr($directory, -5) === '/.git') {
            $directory = substr($directory, 0, -5);
        }

        $directory = basename($directory, '.git');

        if (($pos = strrpos($directory, ':')) !== false) {
            $directory = substr($directory, $pos + 1);
        }

        return $directory;
    }


    /**
     * Is path absolute?
     * Method from Nette\Utils\FileSystem
     * @link   https://github.com/nette/nette/blob/master/Nette/Utils/FileSystem.php
     * @param $path
     * @return bool
     */
    public static function isAbsolute($path) {
        return (bool)preg_match('#[/\\\\]|[a-zA-Z]:[/\\\\]|[a-z][a-z0-9+.-]*://#Ai', $path);
    }
}