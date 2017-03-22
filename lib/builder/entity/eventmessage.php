<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class EventMessage
 * @property int id
 * @property string siteId
 * @property string active
 * @property string emailFrom
 * @property string emailTo
 * @property string subject
 * @property string message
 * @property string bodyType
 * @property string bcc
 * @package WS\Migrations\Builder\Entity
 */
class EventMessage extends Base {
    const BODY_TYPE_TEXT = 'text';
    const BODY_TYPE_HTML = 'html';
    private $forRemove;

    public function __construct($from, $to, $siteId, $data = array()) {
        $this
            ->setEmailFrom($from)
            ->setEmailTo($to)
            ->setSiteId($siteId);

        $this->setSaveData($data);
        $this->dateUpdate = new DateTime();
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'siteId' => 'LID',
            'active' => 'ACTIVE',
            'emailFrom' => 'EMAIL_FROM',
            'emailTo' => 'EMAIL_TO',
            'subject' => 'SUBJECT',
            'message' => 'MESSAGE',
            'bodyType' => 'BODY_TYPE',
            'bcc' => 'BCC',
            'dateUpdate' => 'TIMESTAMP_X',
        );
    }

    /**
     * @param int $id
     * @return EventMessage
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
     * @param string $siteId
     * @return EventMessage
     */
    public function setSiteId($siteId) {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * @param bool $active
     * @return EventMessage
     */
    public function setActive($active) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $emailFrom
     * @return EventMessage
     */
    public function setEmailFrom($emailFrom) {
        $this->emailFrom = $emailFrom;
        return $this;
    }

    /**
     * @param string $emailTo
     * @return EventMessage
     */
    public function setEmailTo($emailTo) {
        $this->emailTo = $emailTo;
        return $this;
    }

    /**
     * @param string $subject
     * @return EventMessage
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param string $message
     * @return EventMessage
     */
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $bodyType
     * @return EventMessage
     */
    public function setBodyType($bodyType) {
        $this->bodyType = $bodyType;
        return $this;
    }

    /**
     * @param string $bcc
     * @return EventMessage
     */
    public function setBcc($bcc) {
        $this->bcc = $bcc;
        return $this;
    }

    public function remove() {
        $this->forRemove = true;
    }

    public function isRemoved() {
        return $this->forRemove;
    }

}