<?php

namespace WS\ReduceMigrations;

class PlatformVersion
{

    /**
     * @var string
     */
    private $owner;

    public function __construct() {
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
