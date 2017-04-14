<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Tests;


class Result {
    private $success;
    private $message;
    private $trace;

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setMessage($value) {
        $this->message = $value;
        return $this;
    }

    public function setTrace($aTrace) {
        $this->trace = $aTrace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrace() {
        return $this->trace;
    }

    /**
     * @return mixed
     */
    public function isSuccess() {
        return $this->success;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setSuccess($value) {
        $this->success = $value;
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