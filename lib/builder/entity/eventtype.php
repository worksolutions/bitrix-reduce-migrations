<?php

namespace WS\ReduceMigrations\Builder\Entity;

use WS\ReduceMigrations\Entities\EventMessageTable;

/**
 * Class EventType
 *
 * @method EventType lid($value) - LID
 * @method EventType sort(int $value) - SORT
 * @method EventType eventName(string $value) - EVENT_NAME
 * @method EventType name(string $value) - NAME
 * @method EventType description(string $value) - DESCRIPTION
 * @package WS\ReduceMigrations\Builder\Entity
 */
class EventType extends Base {

    private $id;
    private $eventMessages;

    public function __construct($eventName, $lid, $data = array()) {
        $this
            ->eventName($eventName)
            ->lid($lid);
    }

    protected function getMap() {
        return array(
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
     * @return string
     */
    public function getEventName() {
        return $this->getAttribute('EVENT_NAME');
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $siteId
     *
     * @return EventMessage
     */
    public function addEventMessage($from, $to, $siteId) {
        $eventMessage = new EventMessage($from, $to, $siteId);
        $this->eventMessages[] = $eventMessage;
        return $eventMessage;
    }

    /**
     *
     * @return EventMessage[]
     */
    public function loadEventMessages() {
        $messages = $this->findMessages();
        foreach ($messages as $message) {
            $eventMessage = new EventMessage($message['EMAIL_FROM'], $message['EMAIL_TO'], $message['LID'], $message);
            $eventMessage->setId($message['ID']);
            $eventMessage->markClean();
            $this->eventMessages[] = $eventMessage;
        }
        return $this->eventMessages;
    }

    /**
     * @return mixed
     */
    public function getEventMessages() {
        return $this->eventMessages;
    }

    /**
     * @return array
     */
    private function findMessages() {
        $res = EventMessageTable::getList(array(
            'filter' => array(
                'EVENT_NAME' => $this->getEventName()
            )
        ));

        return $res->fetchAll();
    }
}