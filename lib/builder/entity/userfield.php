<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class UserField
 * @property int id
 * @property string code
 * @property string entityId
 * @property string userTypeId
 * @property string xmlId
 * @property string multiple
 * @property string required
 * @property string showInFilter
 * @property string showInList
 * @property string editInList
 * @property string searchable
 * @property array editFormLabel
 * @property array listLabel
 * @property array filterLabel
 * @property array settings
 * @property int sort
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

    public function __construct($code, $data = false) {
        $this->code = strtoupper($code);
        $this->enumVariants = array();
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'code' => 'FIELD_NAME',
            'entityId' => 'ENTITY_ID',
            'userTypeId' => 'USER_TYPE_ID',
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
     * @return IblockType
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
     * @param string $code
     * @return UserField
     */
    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    /**
     * @param string $userTypeId
     * @return UserField
     */
    public function setUserTypeId($userTypeId) {
        $this->userTypeId = $userTypeId;
        return $this;
    }

    /**
     * @param string $xmlId
     * @return UserField
     */
    public function setXmlId($xmlId) {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @param bool $multiple
     * @return UserField
     */
    public function setMultiple($multiple) {
        $this->multiple = $multiple ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $required
     * @return UserField
     */
    public function setRequired($required) {
        $this->required = $required ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $showInFilter
     * @return UserField
     */
    public function setShowInFilter($showInFilter) {
        $this->showInFilter = $showInFilter ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $showInList
     * @return UserField
     */
    public function setShowInList($showInList) {
        $this->showInList = $showInList ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $editInList
     * @return UserField
     */
    public function setEditInList($editInList) {
        $this->editInList = $editInList ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $searchable
     * @return UserField
     */
    public function setSearchable($searchable) {
        $this->searchable = $searchable ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param array $label ['ru' => 'поле', 'en' => 'field']
     * @return UserField
     */
    public function setLabel($label) {
        $this->editFormLabel = $label;
        $this->filterLabel = $label;
        $this->listLabel = $label;
        return $this;
    }

    /**
     * @param array $filterLabel
     * @return UserField
     */
    public function setFilterLabel($filterLabel) {
        $this->filterLabel = $filterLabel;
        return $this;
    }

    /**
     * @param array $settings
     * @return UserField
     */
    public function setSettings($settings) {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @param int $sort
     * @return UserField
     */
    public function setSort($sort) {
        $this->sort = $sort;
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
        $variant->del = 'Y';
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
            'USER_FIELD_ID' => $this->id,
            'VALUE' => $name,
        ))->Fetch();
        if (empty($res)) {
            throw new BuilderException('Enum for update not found');
        }
        return $res;
    }

}