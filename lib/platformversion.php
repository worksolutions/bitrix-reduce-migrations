<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations;

class PlatformVersion
{

    /**
     * @var string
     */
    private $owner;

    /**
     * @var array ["Vasiliy Dubinin"]
     */
    private $mapOtherVersions;

    public function __construct($mapOtherVersions) {
        $this->mapOtherVersions = $mapOtherVersions ?: array();
        $filePath = $this->filePath();
        file_exists($filePath) ? $this->initFromFile() : $this->save();
    }

    private function initFromFile() {
        $this->owner = trim(file_get_contents($this->filePath()));
    }


    /**
     * @return bool
     */
    public function isValid() {
        return !empty($this->getOwner());
    }

    /**
     * @return string
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @return array
     */
    public function getMapVersions() {
        return array_merge(
            array($this->getOwner()),
            $this->mapOtherVersions
        );
    }

    public function setOwner($owner) {
        $this->owner = $owner;
        $this->save();
    }

    /**
     * @return string
     */
    private function filePath() {
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/';

        return $docRoot . \COption::GetOptionString("main", "upload_dir", "upload") . "/ws.reducemigrations/version.dat";
    }

    /**
     * @throws \Exception
     */
    private function save() {
        $raw = $this->owner;
        $r = fopen($this->filePath(), 'w');
        $writeRes = fwrite($r, $raw);
        if ($writeRes === false) {
            throw new \Exception("File with migration version data isn`t available to record, path " . $this->filePath());
        }
    }

}
