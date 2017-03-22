<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Tests;


class Result {
    private $_success;
    private $_message;
    private $_trace;

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->_message;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setMessage($value) {
        $this->_message = $value;
        return $this;
    }

    public function setTrace($aTrace) {
        $this->_trace = $aTrace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrace() {
        return $this->_trace;
    }

    /**
     * @return mixed
     */
    public function isSuccess() {
        return $this->_success;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setSuccess($value) {
        $this->_success = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() {
        return array(
            'STATUS' => $this->isSuccess(),
            'MESSAGE' => array(
                'PREVIEW' => str_replace("\n", "<br/>", $this->getMessage()),
                'DETAIL' => $this->getTrace()
            )
        );
    }

}