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
    private $version;

    /**
     * @var string
     */
    private $checkSum;

    /**
     * @var string
     */
    private $owner;

    /**
     * @var array ["asfdsgs" => "Vasiliy Dubinin"]
     */
    private $mapOtherVersions;

    public function __construct($mapOtherVersions) {
        $this->mapOtherVersions = $mapOtherVersions ?: array();
        $filePath = $this->filePath();
        file_exists($filePath) ? $this->initFromFile() : $this->generate();
    }

    private function initFromFile() {
        $raw = explode(':#:', file_get_contents($this->filePath()));
        $this->version = $raw[0];
        $this->checkSum = $raw[1];
        $this->owner = $raw[2];
    }

    private function generate() {
        $this->version = md5(time());
        $this->checkSum = md5($this->version . __FILE__);
        $this->save();
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->checkSum == md5($this->version . __FILE__);
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
            array(
                $this->getValue() => $this->getOwner(),
            ),
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
        $raw = $this->version . ':#:' . md5($this->version . __FILE__) . ':#:' . $this->owner;
        $r = fopen($this->filePath(), 'w');
        $writeRes = fwrite($r, $raw);
        if ($writeRes === false) {
            throw new \Exception("File with migration version data isn`t available to record, path " . $this->filePath());
        }
    }

    public function refresh() {
        $this->generate();
    }
}
