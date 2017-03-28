<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;

use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\factories\DateTimeFactory;

class AppliedChangesLogModel extends BaseEntity {
    const STATUS_SKIPPED = 2;
    const STATUS_SUCCESS = 1;
    const STATUS_NOT_APPLIED = 0;
    public
        $id, $groupLabel, $date, $status,
        $subjectName, $hash, $updateData,
        $owner, $description, $setupLogId;

    private $_setupLog;

    public function __construct() {
        $this->date = DateTimeFactory::createBase();
    }

    public static function findByHash($hash) {
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array(
                'hash' => $hash . '%'
            )
        ));

        return $logs;
    }

    public static function hasMigrationsWithLog($setupLogId) {
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array(
                '=setupLogId' => $setupLogId
            )
        ));

        return !empty($logs);
    }

    public static function createByParams($setupLog, $class) {
        $element = new self();
        $element->subjectName = $class;
        $element->setSetupLog($setupLog);
        $element->groupLabel = $class . '.php';
        $element->description = $class::name();
        $element->hash = $class::hash();
        $element->owner = $class::owner();

        return $element;
    }

    static protected function modifyFromDb($data) {
        $result = array();
        foreach ($data as $name => $value) {
            if ($name == 'date') {
                if ($value instanceof DateTime) {
                    $timestamp = $value->getTimestamp();
                    $value = DateTimeFactory::createBase();
                    $value->setTimestamp($timestamp);
                } else {
                    $value = DateTimeFactory::createBase($value);
                }
            }
            if (in_array($name, array('updateData'))) {
                $value = \WS\ReduceMigrations\jsonToArray($value);
            }
            $result[$name] = $value;
        }
        return $result;
    }

    static protected function modifyToDb($data) {
        $result = array();
        foreach ($data as $name => $value) {
            if ($name == 'date' && $value instanceof \DateTime) {
                $value = DateTimeFactory::createBitrix($value);
            }
            if (in_array($name, array('updateData'))) {
                $value = \WS\ReduceMigrations\arrayToJson($value);
            }
            $result[$name] = $value;
        }
        return $result;
    }

    static protected function map() {
        return array(
            'id' => 'ID',
            'setupLogId' => 'SETUP_LOG_ID',
            'groupLabel' => 'GROUP_LABEL',
            'date' => 'DATE',
            'subjectName' => 'SUBJECT',
            'hash' => 'HASH',
            'owner' => 'OWNER',
            'updateData' => 'UPDATE_DATA',
            'status' => 'STATUS',
            'description' => 'DESCRIPTION'
        );
    }

    /**
     * @return SetupLogModel
     */
    public function getSetupLog() {
        if (!$this->_setupLog) {
            $this->_setupLog = SetupLogModel::findOne(array(
                    'filter' => array('=id' => $this->setupLogId)
                )
            );
        }
        return $this->_setupLog;
    }

    public function setSetupLog(SetupLogModel $model = null) {
        $this->_setupLog = $model;
        $model->id && $this->setupLogId = $model->id;
        return $this;
    }

    static protected function gatewayClass() {
        return AppliedChangesLogTable::className();
    }

    /**
     * @return bool
     */
    public function isSuccessful() {
        return $this->status == self::STATUS_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isFailed() {
        return $this->status == self::STATUS_NOT_APPLIED;
    }

    /**
     * @return bool
     */
    public function isSkipped() {
        return $this->status == self::STATUS_SKIPPED;
    }

    public function markAsFailed() {
        $this->status = self::STATUS_NOT_APPLIED;
    }

    public function markSkipped() {
        $this->status = self::STATUS_SKIPPED;
    }

    public function markAsSuccessful() {
        $this->status = self::STATUS_SUCCESS;
    }
}