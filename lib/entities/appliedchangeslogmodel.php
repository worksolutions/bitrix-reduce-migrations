<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;

use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\factories\DateTimeFactory;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class AppliedChangesLogModel extends BaseEntity {
    const STATUS_SKIPPED = 2;
    const STATUS_SUCCESS = 1;
    const STATUS_NOT_APPLIED = 0;
    public
        $id, $groupLabel, $date, $status,
        $migrationClassName, $hash, $updateData,
        $description, $setupLogId;

    private $setupLog;

    public function __construct() {
        $this->date = DateTimeFactory::createBase();
    }

    /**
     * @param $hash
     *
     * @return AppliedChangesLogModel[]
     */
    public static function findByHash($hash) {
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array(
                'hash' => $hash . '%'
            )
        ));

        return $logs;
    }

    /**
     * @param $hash
     *
     * @return AppliedChangesLogModel[]
     */
    public static function findToHash($hash) {
        $logsByHash = AppliedChangesLogModel::findByHash($hash);

        if (empty($logsByHash)) {
            return array();
        }
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array('>id' => $logsByHash[0]->getId())
        ));

        return $logs;
    }

    /**
     * @return AppliedChangesLogModel[]
     */
    public static function findLastBatch() {
        $setupLog = Module::getInstance()->getLastSetupLog();
        if (!$setupLog) {
            return array();
        }
        return $setupLog->getAppliedLogs() ?: array();
    }

    /**
     * @param int $count
     *
     * @return AppliedChangesLogModel[]
     */
    public static function findLastFewMigrations($count) {
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'limit' => $count,
        ));

        return $logs;
    }

    /**
     * @param $setupLogId
     *
     * @return bool
     */
    public static function hasMigrationsWithLog($setupLogId) {
        $logs = AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array(
                '=setupLogId' => $setupLogId
            )
        ));

        return !empty($logs);
    }

    /**
     * @param $setupLog
     * @param ScriptScenario $class
     *
     * @return AppliedChangesLogModel
     */
    public static function createByParams($setupLog, $class) {
        $element = new self();
        $element->migrationClassName = $class;
        $element->setSetupLog($setupLog);
        $element->groupLabel = $class . '.php';
        $element->setName($class::name());
        $element->hash = $class::hash();

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
            if (in_array($name, array('description', 'updateData'))) {
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
            if (in_array($name, array('description', 'updateData'))) {
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
            'migrationClassName' => 'SUBJECT',
            'hash' => 'HASH',
            'updateData' => 'UPDATE_DATA',
            'status' => 'STATUS',
            'description' => 'DESCRIPTION'
        );
    }

    /**
     * @return string
     */
    public function getHash() {
        return substr($this->hash, 0, ScriptScenario::SHORTENED_HASH_LENGTH);
    }

    /**
     * @param $name
     */
    public function setName($name) {
        $this->description['name'] = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->description['name'] ? : '';
    }

    /**
     * @param $message
     */
    public function setErrorMessage($message) {
        $this->description['errorMessage'] = $message;
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
        return $this->description['errorMessage'];
    }

    /**
     * @param $time - seconds
     */
    public function setTime($time) {
        $this->description['time'] = $time;
    }

    /**
     * @return double
     */
    public function getTime() {
        return $this->description['time'];
    }

    /**
     * @return SetupLogModel
     */
    public function getSetupLog() {
        if (!$this->setupLog) {
            $this->setupLog = SetupLogModel::findOne(array(
                    'filter' => array('=id' => $this->setupLogId)
                )
            );
        }
        return $this->setupLog;
    }

    public function setSetupLog(SetupLogModel $model = null) {
        $this->setupLog = $model;
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

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGroupLabel() {
        return $this->groupLabel;
    }

    /**
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getMigrationClassName() {
        return $this->migrationClassName;
    }

    /**
     * @return array
     */
    public function getUpdateData() {
        return $this->updateData;
    }

    /**
     * @param array $updateData
     */
    public function setUpdateData($updateData) {
        $this->updateData = $updateData;
    }

    /**
     * @return integer
     */
    public function getSetupLogId() {
        return $this->setupLogId;
    }
}