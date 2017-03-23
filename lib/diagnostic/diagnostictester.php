<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Diagnostic;

use WS\ReduceMigrations\Module;


class DiagnosticTester {

    const LOG_TYPE = 'WS_MIGRATIONS_DIAGNOSTIC';

    /**
     * @var Module
     */
    private $module;

    /**
     * @var bool
     */
    private $lastRun;

    /**
     * @param Module $module
     */
    public function __construct( Module $module) {
        $this->module = $module;
    }

    /**
     * @return bool
     */
    public function run() {
        $success = true;
        $messages = array();
        if (!$this->module->getPlatformVersion()->isValid()) {
            $messages[] = new ErrorMessage('module', '', '', 'Module has not valid version');
            $success = false;
        }

        $this->lastRun = $success;
        $jsonData = json_encode(array(
            'success' => $success,
            'messages' => array_map(function (ErrorMessage $message) {
                return $message->toArray();
            }, $messages)
        ));
        \CEventLog::Log('INFO', self::LOG_TYPE, 'ws.reducemigrations', null, $jsonData);
        return $success;
    }

    /**
     * @return bool
     */
    public function hasRun() {
        return $this->lastRun !== null;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isSuccessRunResult() {
        if (!$this->hasRun()) {
            throw new \Exception("Run is not launched");
        }
        return $this->lastRun;
    }

    /**
     * @return DiagnosticResult
     */
    public function getLastResult() {
        $arLog = \CEventLog::GetList(array('ID' => 'DESC'), array(
                'AUDIT_TYPE_ID' => self::LOG_TYPE
            ),
            array(
                'nPageSize' => 1
            )
        )->Fetch();

        if (!$arLog) {
            return DiagnosticResult::createNull();
        }
        $arLogData = $arLog ? json_decode($arLog['DESCRIPTION'], true) : array();
        $res = new DiagnosticResult(
            $arLogData['success'] ?: false,
            array_map(
                function ($messageData) {
                    return ErrorMessage::unpack($messageData);
                },
                $arLogData['messages'] ?: array()
            ),
            $arLog['TIMESTAMP_X']
        );
        return $res;
    }
}