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
        if (is_dir($this->getRepositoryPath() . '/.git') && ($this->getRepositoryPath() !== '.' && $this->getRepositoryPath() !== '..') && is_dir($this->getRepositoryPath())) {
            /*exec(self::processCommand([
                'rm',
                '-rf',
                $this->getRepositoryPath()
            ]), $output, $returnCode);*/

            //exec('rm -rf '.$this->getRepositoryPath().'/', $output, $returnCode);


            if (!$this->deleteDirectory($this->getRepositoryPath())) {
                throw new \Exception('Removing git repository failed (' . $this->getRepositoryPath() . ') - ' . $returnCode . ' - ' . 'rm -rf ' . $this->getRepositoryPath());
            }
        }
    }

    private function deleteDirectory($directory) {
        $files = array_diff(scandir($directory), array('.', '..'));
        foreach ($files as $file) {
            (is_dir($directory . '/' . $file)) ? $this->deleteDirectory($directory . '/' . $file) : unlink($directory . '/' . $file);
        }
        return rmdir($directory);
    }
}