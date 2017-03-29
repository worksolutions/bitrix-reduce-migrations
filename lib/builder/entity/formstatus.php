<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class FormStatus
 * @property int id
 * @property int sort
 * @property string active
 * @property string title
 * @property string description
 * @property string isDefault
 * @property string css
 * @property string handlerOut
 * @property string handlerIn
 * @property array arGroupCanView
 * @property array arGroupCanMove
 * @property array arGroupCanEdit
 * @property array arGroupCanDelete
 * @package WS\ReduceMigrations\Builder\Entity
 */
class FormStatus extends Base {

    public function __construct($title, $data = array()) {
        $this->title = $title;
        $this->setSaveData($data);
        $this->dateUpdate = new DateTime();
    }

    public function getMap() {
        return array(
            'id' => 'ID',
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
     * @param int $sort
     * @return FormStatus
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param bool $active
     * @return FormStatus
     */
    public function setActive($active) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $title
     * @return FormStatus
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $description
     * @return FormStatus
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param bool $isDefault
     * @return FormStatus
     */
    public function setIsDefault($isDefault) {
        $this->isDefault = $isDefault ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $css
     * @return FormStatus
     */
    public function setCss($css) {
        $this->css = $css;
        return $this;
    }

    /**
     * @param string $handlerOut
     * @return FormStatus
     */
    public function setHandlerOut($handlerOut) {
        $this->handlerOut = $handlerOut;
        return $this;
    }

    /**
     * @param string $handlerIn
     * @return FormStatus
     */
    public function setHandlerIn($handlerIn) {
        $this->handlerIn = $handlerIn;
        return $this;
    }

    /**
     * @param array $arGroupCanView
     * @return FormStatus
     */
    public function setArGroupCanView($arGroupCanView) {
        $this->arGroupCanView = $arGroupCanView;
        return $this;
    }

    /**
     * @param array $arGroupCanMove
     * @return FormStatus
     */
    public function setArGroupCanMove($arGroupCanMove) {
        $this->arGroupCanMove = $arGroupCanMove;
        return $this;
    }

    /**
     * @param array $arGroupCanEdit
     * @return FormStatus
     */
    public function setArGroupCanEdit($arGroupCanEdit) {
        $this->arGroupCanEdit = $arGroupCanEdit;
        return $this;
    }

    /**
     * @param array $arGroupCanDelete
     * @return FormStatus
     */
    public function setArGroupCanDelete($arGroupCanDelete) {
        $this->arGroupCanDelete = $arGroupCanDelete;
        return $this;
    }

}