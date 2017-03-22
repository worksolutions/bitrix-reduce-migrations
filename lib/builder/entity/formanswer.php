<?php

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class FormAnswer
 * @property int id
 * @property int sort
 * @property string active
 * @property string message
 * @property string value
 * @property string fieldType
 * @property string fieldWidth
 * @property string fieldHeight
 * @property string fieldParam
 * @package WS\Migrations\Builder\Entity
 */
class FormAnswer extends Base {

    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_DATE = 'date';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_PASSWORD = 'password';
    public function __construct($message, $data = array()) {
        $this->message = $message;
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'sort' => 'C_SORT',
            'message' => 'MESSAGE',
            'value' => 'VALUE',
            'active' => 'ACTIVE',
            'fieldType' => 'FIELD_TYPE',
            'fieldWidth' => 'FIELD_WIDTH',
            'fieldHeight' => 'FIELD_HEIGHT',
            'fieldParam' => 'FIELD_PARAM',
            'del' => 'DEL',
        );
    }

    /**
     * @param int $id
     * @return FormAnswer
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
     * @return FormAnswer
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param bool $active
     * @return FormAnswer
     */
    public function setActive($active) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $message
     * @return FormAnswer
     */
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $value
     * @return FormAnswer
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $fieldType
     * @return FormAnswer
     */
    public function setFieldType($fieldType) {
        $this->fieldType = $fieldType;
        return $this;
    }

    /**
     * @param string $fieldWidth
     * @return FormAnswer
     */
    public function setFieldWidth($fieldWidth) {
        $this->fieldWidth = $fieldWidth;
        return $this;
    }

    /**
     * @param string $fieldHeight
     * @return FormAnswer
     */
    public function setFieldHeight($fieldHeight) {
        $this->fieldHeight = $fieldHeight;
        return $this;
    }

    /**
     * @param string $fieldParam
     * @return FormAnswer
     */
    public function setFieldParam($fieldParam) {
        $this->fieldParam = $fieldParam;
        return $this;
    }

    public function needDelete() {
        return $this->del == 'Y';
    }


}