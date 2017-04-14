<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations;


class ApplyResult {
    private $success;
    private $message;
    private $id;

    /**
     * @param string $value
     * @return $this
     */
    public function setMessage($value) {
        $this->message = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return boolean
     */
    public function isSuccess() {
        return $this->success;
    }

    /**
     * @param boolean $value
     * @return $this
     */
    public function setSuccess($value) {
        $this->success = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

}