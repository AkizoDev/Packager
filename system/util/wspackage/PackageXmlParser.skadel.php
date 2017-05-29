<?php


namespace skadel\system\util\wspackage;

class PackageXmlParser {

    /**
     * A list of all PIPs shipped with WSC and their default file names.
     *
     * @see https://github.com/WoltLab/WCF/tree/master/wcfsetup/install/files/lib/system/package/plugin
     */
    const DEFAULT_PIP_FILENAMES = [
        'aclOption' => 'aclOption.xml',
        'acpMenu' => 'acpMenu.xml',
        'acpSearchProvider' => 'acpSearchProvider.xml',
        'acpTemplate' => 'acptemplates.tar',
        'bbcode' => 'bbcode.xml',
        'box' => 'box.xml',
        'clipboard' => 'clipboard.xml',
        'coreObject' => 'coreObject.xml',
        'cronjob' => 'cronjob.xml',
        'eventListener' => 'eventListener.xml',
        'file' => 'files.tar',
        'language' => 'language/*.xml',
        'menu' => 'menu.xml',
        'menuitem' => 'menuitem.xml',
        'objectType' => 'objectType.xml',
        'objectTypeDefinition' => 'objectTypeDefinition.xml',
        'option' => 'option.xml',
        'page' => 'page.xml',
        'pip' => 'packageInstallationPlugin.xml',
        'script' => null,
        'smiley' => 'smiley.xml',
        'sql' => 'install.sql',
        'style' => null,
        'template' => 'templates.tar',
        'templateListener' => 'templateListener.xml',
        'userGroupOption' => 'userGroupOption.xml',
        'userOption' => 'userOption.xml',
        'userProfileMenu' => 'userProfileMenu.xml',
        'userNotificationEvent' => 'userNotificationEvent.xml'
    ];

    protected $packagePath = null;
    private $xml = null;
    private $pips = [];
    private $data = [];
    private $files = [];

    public function __construct($packagePath) {
        $this->packagePath = $packagePath;

        $this->parse();
    }

    protected function parse() {
        if ($this->packagePath !== null && is_file($this->packagePath . '/package.xml')) {
            try {

                $this->xml = new \SimpleXMLElement(file_get_contents($this->packagePath . '/package.xml'));

                $this->data = [
                    'name' => (string)$this->xml->attributes()->name,
                    'version' => (string)$this->xml->packageinformation->version,
                    'author' => (string)$this->xml->authorinformation->author
                ];

                $tmpFiles = [];
                /** @var \SimpleXMLElement $instruction */
                foreach ($this->xml->instructions->instruction as $instruction) {
                    $tmpFiles[] = [
                        'pip' => (string)$instruction->attributes()->type,
                        'path' => (string)$instruction
                    ];
                }

                $tmpPackages = [];
                foreach ($this->xml->requiredpackages->requiredpackage as $required) {
                    $file = (string)$required->attributes()->file;
                    if (!empty($file)) {
                        $tmpPackages[] = $file;
                    }
                }
                foreach ($this->xml->optionalpackage->optionalpackage as $optional) {
                    $file = (string)$optional->attributes()->file;
                    if (!empty($file)) {
                        $tmpPackages[] = $file;
                    }
                }
                $this->files = array_merge($this->parsePips($tmpFiles), $tmpPackages, ['package.xml']);
                //TODO: parse style.xml -> pack files for styles

            } catch (\Exception $e) {
                echo 'package.xml is not a valid xml file';
            }
        } else {
            echo 'package.xml not found';
        }
        return false;
    }

    public function getInformation() {
        return $this->data;
    }

    public function getFiles() {
        return $this->files;
    }

    protected function parsePips($instructions) {
        $this->pips = array_merge(static::DEFAULT_PIP_FILENAMES, CUSTOM_PIPS);
        return array_filter(array_map([&$this, 'getFileName'], $instructions), function ($e) { return ($e !== null); });
    }

    protected function getFileName($data) {
        if (isset($data['path']) && $data['path'] !== '') {
            return $data['path'];
        }

        if (isset($this->pips[$data['pip']])) {
            return $this->pips[$data['pip']];
        }

        return null;
    }
}