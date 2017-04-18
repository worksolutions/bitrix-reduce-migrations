<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class UserField
 * 
 * @method  UserField code(string $value)
 * @method UserField entityId
 * @method UserField type(string $value)
 * @method UserField xmlId(string $value)
 * @method UserField editFormLabel(array $value) - ['ru' => 'name', 'en' => 'name']
 * @method UserField listLabel(array $value) - ['ru' => 'name', 'en' => 'name']
 * @method UserField filterLabel(array $value) - ['ru' => 'name', 'en' => 'name']
 * @method UserField settings(array $value)
 * @method UserField sort(int $value)
 *                         
 * @package WS\ReduceMigrations\Builder\Entity
 */
class UserField extends Base {

    const TYPE_VIDEO = 'video';
    const TYPE_HLBLOCK = 'hlblock';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_DOUBLE = 'double';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATE = 'date';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_FILE = 'file';
    const TYPE_ENUMERATION = 'enumeration';
    const TYPE_IBLOCK_SECTION = 'iblock_section';
    const TYPE_IBLOCK_ELEMENT = 'iblock_element';
    const TYPE_STRING_FORMATTED = 'string_formatted';
    const TYPE_VOTE = 'vote';
    private $enumVariants;
    private $id;

    public function __construct($code) {
        $this->code(strtoupper($code));
        $this->enumVariants = array();
    }

    public function getMap() {
        return array(
            'code' => 'FIELD_NAME',
            'entityId' => 'ENTITY_ID',
            'type' => 'USER_TYPE_ID',
            'xmlId' => 'XML_ID',
            'sort' => 'SORT',
            'multiple' => 'MULTIPLE',
            'required' => 'MANDATORY',
            'showInFilter' => 'SHOW_FILTER',
            'showInList' => 'SHOW_IN_LIST',
            'editInList' => 'EDIT_IN_LIST',
            'searchable' => 'IS_SEARCHABLE',
            'editFormLabel' => 'EDIT_FORM_LABEL',
            'listLabel' => 'LIST_COLUMN_LABEL',
            'filterLabel' => 'LIST_FILTER_LABEL',
            'settings' => 'SETTINGS',
        );
    }

    /**
     * @param int $id
     * @return UserField
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
     * @param $label
     *
     * @return UserField $this
     */
    public function label($label) {
        $this->listLabel($label);
        $this->editFormLabel($label);
        $this->filterLabel($label);
        return $this;
    }
    /**
     * @param bool $multiple
     * @return UserField
     */
    public function multiple($multiple) {
        $this->setAttribute('MULTIPLE', $multiple ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $required
     * @return UserField
     */
    public function required($required) {
        $this->setAttribute('MANDATORY', $required ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $showInFilter
     * @return UserField
     */
    public function showInFilter($showInFilter) {
        $this->setAttribute('SHOW_FILTER', $showInFilter ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $showInList
     * @return UserField
     */
    public function showInList($showInList) {
        $this->setAttribute('SHOW_IN_LIST', $showInList ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $editInList
     * @return UserField
     */
    public function editInList($editInList) {
        $this->setAttribute('EDIT_IN_LIST', $editInList ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $searchable
     * @return UserField
     */
    public function searchable($searchable) {
        $this->setAttribute('IS_SEARCHABLE', $searchable ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param $name
     * @return EnumVariant
     */
    public function addEnum($name) {
        $variant = new EnumVariant($name);
        $this->enumVariants[] = $variant;
        return $variant;
    }

    /**
     * @param $name
     * @return EnumVariant
     */
    public function updateEnum($name) {
        $data = $this->findEnum($name);
        $variant = new EnumVariant($name, $data);
        $variant->markClean();
        $this->enumVariants[] = $variant;
        return $variant;
    }

    /**
     * @param $name
     * @return UserField
     */
    public function removeEnum($name) {
        $data = $this->findEnum($name);
        $variant = new EnumVariant($name, $data);
        $variant->markDeleted();
        $this->enumVariants[] = $variant;
        return $this;
    }

    /**
     * @return EnumVariant[]
     */
    public function getEnumVariants() {
        return $this->enumVariants;
    }

    /**
     * @param $name
     * @return array
     * @throws BuilderException
     */
    private function findEnum($name) {
        if (!$this->getId()) {
            throw new BuilderException('Save Field before update enum');
        }
        $res = \CUserFieldEnum::GetList(null, array(
            'USER_FIELD_ID' => $this->getId(),
            'VALUE' => $name,
        ))->Fetch();
        if (empty($res)) {
            throw new BuilderException("Enum for `$name` not found");
        }
        return $res;
    }

}