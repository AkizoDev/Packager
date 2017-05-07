<?php


namespace skadel\system\util\wspackage;


class Packager extends PackageXmlParser {
    public function __construct($packagePath) {
        parent::__construct($packagePath);
    }

    public function build() {
        $package = $this->getInformation();
        $files = $this->getFiles();

        $packageName = $this->formatPackageName($package);


        /* pack required .tar files */
        foreach ($files as $file) {
            if (static::endsWith($file, '.tar')) {
                $pack = new \PharData($this->packagePath . '/' . $file);
                $pack->buildFromDirectory($this->packagePath . '/' . str_replace('.tar', '', $file));
            }
        }

        //TODO: pack style files (if needed)

        /* pack final package */
        if (file_exists($this->packagePath . '/' . $packageName . '.tar')) {
            unlink($this->packagePath . '/' . $packageName . '.tar');
        }

        /* Strange behaviour of the compress function so we don't support .tar.gz yet
        if (file_exists($this->packagePath . '/' . $packageName . '.tar.gz')) {
            unlink($this->packagePath . '/' . $packageName . '.tar.gz');
        }*/

        $phar = new \PharData($this->packagePath . '/' . $packageName . '.tar');
        foreach ($files as $file) {
            /* find all files where an wildcard is used */
            if (strpos($file, '*.') !== false) {
                $tmpFiles = array_diff(glob(PACKAGE_DIR . $file), ['.', '..']);
                foreach ($tmpFiles as $tmpFile) {
                    $tmpFileData = pathinfo($file);
                    $phar->addFile($tmpFile, str_replace('*', $tmpFileData['filename'], $file));
                }
            } else {
                $phar->addFile($this->packagePath . '/' . $file, $file);
            }
        }
        //$phar->compress(\Phar::GZ);

        return array_merge($package, [
            'file' => $this->packagePath . '/' . $packageName . '.tar',
            //'fileGZ' => $this->packagePath . '/' . $packageName . '.tar.gz',
        ]);
    }

    protected
    function formatPackageName($data) {
        return str_replace(' ', '_', $data['name'] . '_' . $data['version']);
    }

    private
    static function endsWith($haystack, $needle, $ci = false) {
        if ($ci) {
            $haystack = mb_strtolower($haystack);
            $needle = mb_strtolower($needle);
        }
        $length = mb_strlen($needle);
        if ($length === 0) {
            return true;
        }
        return (mb_substr($haystack, $length * -1) === $needle);
    }
}