<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class EventMessage
 *
 * @method EventMessage siteId(string|array $value)
 * @method EventMessage emailFrom(string $value)
 * @method EventMessage emailTo(string $value)
 * @method EventMessage subject(string $value)
 * @method EventMessage body(string $value)
 * @method EventMessage bodyType(string $value)
 * @method EventMessage bcc(string $value)
 * @method EventMessage dateUpdate(\Bitrix\Main\Type\DateTime $value)
 * @package WS\ReduceMigrations\Builder\Entity
 */
class EventMessage extends Base {
    const BODY_TYPE_TEXT = 'text';
    const BODY_TYPE_HTML = 'html';
    private $forRemove;
    private $id;

    public function __construct($from, $to, $siteId, $data = array()) {
        foreach ($data as $code => $value) {
            $this->setAttribute($code, $value);
        }
        $this
            ->emailFrom($from)
            ->active()
            ->emailTo($to)
            ->siteId($siteId)
            ->dateUpdate(new DateTime());

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

    protected function getMap() {
        return array(
            'id' => 'ID',
            'siteId' => 'LID',
            'active' => 'ACTIVE',
            'emailFrom' => 'EMAIL_FROM',
            'emailTo' => 'EMAIL_TO',
            'subject' => 'SUBJECT',
            'body' => 'MESSAGE',
            'bodyType' => 'BODY_TYPE',
            'bcc' => 'BCC',
            'dateUpdate' => 'TIMESTAMP_X',
        );
    }

    /**
     * @param bool $active
     * @return EventMessage
     */
    public function active($active = true) {
        $this->setAttribute('ACTIVE', $active ? 'Y' : 'N');
        return $this;
    }

    public function remove() {
        $this->forRemove = true;
    }

    public function isRemoved() {
        return $this->forRemove;
    }

}