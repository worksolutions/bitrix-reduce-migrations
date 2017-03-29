<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class Agent
 * @property int id
 * @property int sort
 * @property int userId
 * @property int interval
 * @property string active
 * @property string module
 * @property string callback
 * @property string isPeriod
 * @property string nextExec d.m.Y H:i:s
 * @package WS\ReduceMigrations\Builder\Entity
 */
class Agent extends Base {

    public function __construct($callback, $data = array()) {
        $this->callback = $callback;
        $this->setDefault();
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'sort' => 'SORT',
            'active' => 'ACTIVE',
            'module' => 'MODULE_ID',
            'callback' => 'NAME',
            'userId' => 'USER_ID',
            'isPeriod' => 'IS_PERIOD',
            'interval' => 'AGENT_INTERVAL',
            'nextExec' => 'NEXT_EXEC',
        );
    }

    /**
     * @param int $id
     * @return Agent
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $sort
     * @return Agent
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    private function setDefault() {
        $this
            ->setSort(100)
            ->setActive(true)
            ->setInterval(86400)
            ->setIsPeriod(false)
            ->setNextExec(new DateTime());
    }

    /**
     * @param int $userId
     * @return Agent
     */
    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param int $interval
     * @return Agent
     */
    public function setInterval($interval) {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @param bool $active
     * @return Agent
     */
    public function setActive($active) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $module
     * @return Agent
     */
    public function setModule($module) {
        $this->module = $module;
        return $this;
    }

    /**
     * @param string $callback
     * @return Agent
     */
    public function setCallback($callback) {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @param bool $isPeriod
     * @return Agent
     */
    public function setIsPeriod($isPeriod) {
        $this->isPeriod = $isPeriod ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param DateTime $nextExec
     * @return Agent
     */
    public function setNextExec($nextExec) {
        $this->nextExec = $nextExec->format('d.m.Y H:i:s');
        return $this;
    }

}