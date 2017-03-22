<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\ChangeDataCollector;


use WS\ReduceMigrations\Processes\BaseProcess;
use WS\ReduceMigrations\SubjectHandlers\BaseSubjectHandler;

class CollectorFix {
    private $_subject;
    private $_process;
    private $_data;
    private $_label;
    private $_name;
    private $_originalData;
    private $_owner;

    private $_isUses = false;

    private $_dbVersion;

    public function __construct($label) {
        $this->_label = $label;
    }

    /**
     * @return $this
     */
    public function take() {
        $this->_isUses = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUses() {
        return $this->_isUses;
    }


    /**
     * @return BaseProcess
     */
    public function getProcess() {
        return $this->_process;
    }


    /**
     * @return BaseSubjectHandler
     */
    public function getSubject() {
        return $this->_subject;
    }

    /**
     * @return mixed
     */
    public function getUpdateData() {
        return $this->_data;
    }

    /**
     * @param mixed $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * @param mixed $process
     * @return $this
     */
    public function setProcess($process) {
        $this->_process = $process;
        return $this;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setUpdateData($data) {
        $this->take();
        $this->_data = $data;
        return $this;
    }

    public function setOriginalData($data) {
        $this->_originalData = $data;
        return $this;
    }

    public function getOriginalData() {
        return $this->_originalData;
    }

    public function getLabel() {
        return $this->_label;
    }

    public function setName($value) {
        $this->_name = $value;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDbVersion($value) {
        $this->_dbVersion = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbVersion() {
        return $this->_dbVersion ?: $this->_data['dbVersion'];
    }

    /**
     * @return string
     */
    public function getOwner() {
        return $this->_owner;
    }

    /**
     * @param string $owner
     * @return $this
     */
    public function setOwner($owner) {
        $this->_owner = $owner;
        return $this;
    }
}
