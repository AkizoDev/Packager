<?php


namespace skadel\system\util;

require_once SYS_DIR . 'lib/Git.php';

class Git extends \GitRepository {
    public static function cloneRepository($url, $directory = null, array $params = null) {
        if ($directory !== null) {
            if (is_dir($directory)) {
                $tmp = new static($directory);
                $tmp->removeLocalRepo();

                /* wait until the local repository is deleted */
                while (is_dir($directory . '/.git')) {
                    sleep(1);
                }
            }
        }

        return parent::cloneRepository($url, $directory, $params);
    }

    public function removeLocalRepo() {
        if (is_dir($this->getRepositoryPath() . '/.git') && ($this->getRepositoryPath() !== '.' && $this->getRepositoryPath() !== '..')) {
            exec(self::processCommand([
                'rm',
                '-r',
                $this->getRepositoryPath()
            ]), $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("Git clone failed (directory $directory).");
            }
        }
    }
}