<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class FormStatus
 * @method FormStatus sort(int $value)
 * @method FormStatus title(string $value)
 * @method FormStatus description(string $value)
 * @method FormStatus css(string $value)
 * @method FormStatus handlerOut(string $value)
 * @method FormStatus handlerIn(string $value)
 * @method FormStatus arGroupCanView(array $value)
 * @method FormStatus arGroupCanMove(array $value)
 * @method FormStatus arGroupCanEdit(array $value)
 * @method FormStatus arGroupCanDelete(array $value)
 * @method FormStatus dateUpdate(\Bitrix\Main\Type\DateTime $value)
 * @package WS\ReduceMigrations\Builder\Entity
 */
class FormStatus extends Base {

    private $id;

    public function __construct($title) {
        $this
            ->title($title)
            ->dateUpdate(new DateTime());
    }

    public function getMap() {
        return array(
            'sort' => 'C_SORT',
            'dateUpdate' => 'TIMESTAMP_X',
            'active' => 'ACTIVE',
            'title' => 'TITLE',
            'description' => 'DESCRIPTION',
            'isDefault' => 'DEFAULT_VALUE',
            'css' => 'CSS',
            'handlerOut' => 'HANDLER_OUT',
            'handlerIn' => 'HANDLER_IN',
            'arGroupCanView' => 'arPERMISSION_VIEW',
            'arGroupCanMove' => 'arPERMISSION_MOVE',
            'arGroupCanEdit' => 'arPERMISSION_EDIT',
            'arGroupCanDelete' => 'arPERMISSION_DELETE',
        );
    }

    /**
     * @param int $id
     * @return FormStatus
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
     * @param bool $active
     * @return FormStatus
     */
    public function active($active) {
        $this->setAttribute('ACTIVE', $active ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $isDefault
     * @return FormStatus
     */
    public function byDefault($isDefault = true) {
        $this->setAttribute('DEFAULT_VALUE', $isDefault ? 'Y' : 'N');
        return $this;
    }

}