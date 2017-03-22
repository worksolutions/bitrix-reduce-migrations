<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\EventMessage;
use WS\ReduceMigrations\Builder\Entity\EventType;
use WS\ReduceMigrations\Entities\EventMessageTable;

class EventsBuilder {
    /** @var  EventType */
    private $eventType;
    /** @var  EventMessage[] */
    private $newMessages;
    /** @var  EventMessage[] */
    private $exitsMessages;

    public function reset() {
        $this->eventType = null;
        $this->newMessages = array();
        $this->exitsMessages = array();
    }

    /**
     * @param $type
     * @param $lid
     * @return EventType
     * @throws BuilderException
     */
    public function addEventType($type, $lid) {
        if ($this->eventType) {
            throw new BuilderException('EventType already set');
        }
        $this->eventType = new EventType($type, $lid);
        return $this->eventType;
    }

    /**
     * @param $type
     * @param $lid
     * @return EventType
     * @throws BuilderException
     */
    public function getEventType($type, $lid) {
        if ($this->eventType) {
            throw new BuilderException('EventType already set');
        }
        $this->eventType = new EventType($type, $lid, $this->findEventType($type, $lid));
        return $this->eventType;
    }

    /**
     * @param $from
     * @param $to
     * @param $siteId
     * @return EventMessage
     */
    public function addEventMessage($from, $to, $siteId) {
        $message = new EventMessage($from, $to, $siteId);
        $this->newMessages[] = $message;
        return $message;
    }

    /**
     * @return Entity\EventMessage[]
     * @throws BuilderException
     */
    public function getEventMessages() {
        foreach ($this->findMessages() as $data) {
            $this->exitsMessages[] = new EventMessage(false, false, false, $data);
        }
        return $this->exitsMessages;
    }

    /**
     * @return EventType
     */
    public function getCurrentEventType() {
        return $this->eventType;
    }

    /**
     * @throws BuilderException
     */
    public function commit() {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitEventType();
            $this->commitNewEventMessages();
            $this->commitExistsEventMessages();
        } catch (\Exception $e) {
            $DB->Rollback();
            throw new BuilderException($e->getMessage());
        }
        $DB->Commit();
    }

    /**
     * @param $type
     * @param $lid
     * @return array
     * @throws BuilderException
     */
    private function findEventType($type, $lid) {
        $data = \CEventType::GetList(array(
            'TYPE_ID' => $type,
            'LID' => $lid
        ))->Fetch();
        if (empty($data)) {
            throw new BuilderException("EventType '{$type}' not found for lid '{$lid}'");
        }
        return $data;
    }

    /**
     * @throws BuilderException
     */
    private function commitEventType() {
        global $APPLICATION;
        if (!$this->eventType) {
            throw new BuilderException("EventType doesn't set");
        }
        $gw = new \CEventType();
        if ($this->eventType->getId() > 0) {
            $gw->Update(['ID' => $this->eventType->getId()], $this->eventType->getSaveData());
        } else {
            $res = $gw->Add($this->eventType->getSaveData());
            if (!$res) {
                throw new BuilderException('EventType add failed with error: ' . $APPLICATION->GetException()->GetString());
            }
            $this->eventType->setId($res);
        }
    }

    /**
     * @throws BuilderException
     */
    private function commitNewEventMessages() {
        global $APPLICATION;
        if (!$this->getCurrentEventType()->getId()) {
            throw new BuilderException("EventType doesn't set");
        }
        $gw = new \CEventMessage();
        foreach ($this->newMessages as $message) {
            $id = $gw->Add(array_merge(
                $message->getSaveData(),
                array('EVENT_NAME' => $this->getCurrentEventType()->eventName)
            ));
            if (!$id) {
               throw new BuilderException("EventMessage add failed with error: " . $APPLICATION->GetException()->GetString());
            }
            $message->setId($id);
        }
    }

    /**
     * @throws BuilderException
     */
    private function commitExistsEventMessages() {
        global $APPLICATION;
        if (!$this->getCurrentEventType()->getId()) {
            throw new BuilderException("EventType doesn't set");
        }
        $gw = new \CEventMessage();
        foreach ($this->exitsMessages as $message) {
            if (!$message->isRemoved()) {
               continue;
            }
            if (!$gw->Delete($message->getId())) {
                throw new BuilderException("EventType wasn't deleted: ". $APPLICATION->GetException()->GetString());
            }
        }
        foreach ($this->exitsMessages as $message) {
            if ($message->isRemoved()) {
                continue;
            }
            if (!$gw->Update($message->getId(), $message->getSaveData())) {
                throw new BuilderException("EventType wasn't updated: ". $APPLICATION->GetException()->GetString());
            }
        }
    }

    /**
     * @return array
     * @throws BuilderException
     * @throws \Bitrix\Main\ArgumentException
     */
    private function findMessages() {
        if (!$this->getCurrentEventType()->getId()) {
            throw new BuilderException("EventType doesn't set");
        }
        $res = EventMessageTable::getList(array(
            'filter' => array(
                'EVENT_NAME' => $this->getCurrentEventType()->eventName
            )
        ));
        return $res->fetchAll();
    }


}
