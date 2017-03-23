<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Diagnostic;

/**
 * Class DiagnosticResult
 *
 * @package WS\ReduceMigrations\Diagnostic
 */
class DiagnosticResult {

    /**
     * @var bool
     */
    private $success;

    /**
     * @var ErrorMessage[]
     */
    private $messages;

    /**
     * @var string
     */
    private $time;

    /**
     * @var bool
     */
    private $isNull = false;

    /**
     * @param bool $success
     * @param ErrorMessage[] $messages
     * @param string $time
     * @throws \Exception
     */
    public function __construct($success, array $messages, $time = '') {
        $this->success = $success;
        $this->messages = $messages;
        foreach ($this->messages as $message) {
            if (! $message instanceof ErrorMessage) {
                throw new \Exception('Message must be as object');
            }
        }
        $this->time = $time;
    }

    /**
     * @return DiagnosticResult
     */
    public static function createNull() {
        $object = new static(true, array(), '-');
        $object->isNull = true;
        return $object;
    }

    /**
     * @return bool
     */
    public function isNull() {
        return $this->isNull;
    }

    /**
     * @return bool
     */
    public function isSuccess() {
        return $this->success;
    }

    /**
     * @return ErrorMessage[]
     */
    public function getMessages() {
        return $this->messages;
    }

    public function getMessagesText() {
        return array_map(function (ErrorMessage $message) {
            return $message->getText();
        }, $this->getMessages());
    }

    /**
     * @return string
     */
    public function getTime() {
        return $this->time;
    }
}