<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\EventMessage;
use WS\ReduceMigrations\Builder\Entity\EventType;

class EventsBuilder {

    /**
     * @param string $type
     * @param string $lid
     * @param \Closure $callback
     * @return EventType
     * @throws BuilderException
     */
    public function createEventType($type, $lid, $callback) {
        $eventType = new EventType($type, $lid);
        $callback($eventType);
        $this->commit($eventType);
        return $eventType;
    }

    /**
     * @param string $type
     * @param string $lid
     * @param \Closure $callback
     * @return EventType
     * @throws BuilderException
     */
    public function updateEventType($type, $lid, $callback) {
        $data = $this->findEventType($type, $lid);
        $eventType = new EventType($data['EVENT_NAME'], $data['LID']);
        $eventType->setId($data['ID']);
        $eventType->markClean();
        $callback($eventType);
        $this->commit($eventType);
        return $eventType;
    }

    /**
     * @param EventType $eventType
     *
     * @throws BuilderException
     */
    public function commit($eventType) {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitEventType($eventType);
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
     * @param EventType $eventType
     *
     * @throws BuilderException
     */
    private function commitEventType($eventType) {
        global $APPLICATION;

        $gw = new \CEventType();
        if ($eventType->getId() > 0) {
            if ($eventType->isDirty()) {
                $result = $gw->Update(array('ID' => $eventType->getId()), $eventType->getData());
                if (!$result) {
                    throw new BuilderException('EventType update failed with error: ' . $APPLICATION->GetException()->GetString());
                }
            }

        } else {
            $result = $gw->Add($eventType->getData());
            if (!$result) {
                throw new BuilderException('EventType add failed with error: ' . $APPLICATION->GetException()->GetString());
            }
            $eventType->setId($result);
        }
        $this->commitEventMessages($eventType->getEventName(), $eventType->getEventMessages());
    }

    /**
     * @param string $eventName
     * @param EventMessage[] $eventMessages
     *
     * @throws BuilderException
     */
    private function commitEventMessages($eventName, $eventMessages) {
        global $APPLICATION;

        $gw = new \CEventMessage();
        foreach ($eventMessages as $message) {
            if ($message->getId() > 0) {
                if ($message->isRemoved() && !$gw->Delete($message->getId())) {
                    throw new BuilderException("EventType wasn't deleted: ". $APPLICATION->GetException()->GetString());
                }
                if ($message->isDirty() && !$gw->Update($message->getId(), $message->getData())) {
                    throw new BuilderException("EventType wasn't updated: ". $APPLICATION->GetException()->GetString());
                }
            } else {
                $id = $gw->Add(array_merge(
                    $message->getData(),
                    array('EVENT_NAME' => $eventName)
                ));
                if (!$id) {
                    throw new BuilderException("EventMessage add failed with error: " . $APPLICATION->GetException()->GetString());
                }
                $message->setId($id);
            }

        }
    }

}
