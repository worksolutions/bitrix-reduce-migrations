<?php

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class EventType
 * @property int id
 * @property int sort
 * @property string eventName
 * @property string lid
 * @property string name
 * @property string description
 * @package WS\Migrations\Builder\Entity
 */
class EventType extends Base {

    public function __construct($eventName, $lid, $data = array()) {
        $this->eventName = $eventName;
        $this->lid = $lid;
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'sort' => 'SORT',
            'eventName' => 'EVENT_NAME',
            'lid' => 'LID',
            'name' => 'NAME',
            'description' => 'DESCRIPTION',
        );
    }

    /**
     * @param int $id
     * @return EventType
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $sort
     * @return EventType
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param string $eventName
     * @return EventType
     */
    public function setEventName($eventName) {
        $this->eventName = $eventName;
        return $this;
    }

    /**
     * @param string $lid
     * @return EventType
     */
    public function setLid($lid) {
        $this->lid = $lid;
        return $this;
    }

    /**
     * @param string $name
     * @return EventType
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $description
     * @return EventType
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

}