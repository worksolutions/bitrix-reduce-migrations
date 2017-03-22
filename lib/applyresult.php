<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations;


class ApplyResult {
    private $_success;
    private $_message;
    private $_id;

    /**
     * @param string $value
     * @return $this
     */
    public function setMessage($value) {
        $this->_message = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->_message;
    }

    /**
     * @return boolean
     */
    public function isSuccess() {
        return $this->_success;
    }

    /**
     * @param boolean $value
     * @return $this
     */
    public function setSuccess($value) {
        $this->_success = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

}