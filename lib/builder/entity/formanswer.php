<?php

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class FormAnswer
 * @method FormAnswer sort(int $value)
 * @method FormAnswer message(string $value)
 * @method FormAnswer value(string $value)
 * @method FormAnswer fieldType(string $value)
 * @method FormAnswer fieldWidth($value)
 * @method FormAnswer fieldHeight($value)
 * @method FormAnswer fieldParam(string $value)
 * @package WS\ReduceMigrations\Builder\Entity
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
    private $id;

    public function __construct($message) {
        $this->message($message);
    }

    public function getMap() {
        return array(
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
     * @param bool $active
     * @return FormAnswer
     */
    public function active($active) {
        $this->setAttribute('ACTIVE', $active ? 'Y' : 'N');
        return $this;
    }

    public function needDelete() {
        return $this->getAttribute('DEL') == 'Y';
    }

    public function markDelete() {
        $this->setAttribute('DEL', 'Y');
    }


}