<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Builder\BuilderException;
use WS\ReduceMigrations\Builder\Traits\ContainUserFieldsTrait;

/**
 * Class Iblock
 *
 * @method Iblock name(string $value) - set NAME
 * @method Iblock code(string $value) - set CODE
 * @method Iblock iblockType(string $value) - set IBLOCK_TYPE_ID
 * @method Iblock sort(integer $value) - set SORT
 * @method Iblock siteId($value) - set SITE_ID array or string
 * @method Iblock groupId(array $value) - set GROUP_ID, e.g [2 => 'R']
 * @method Iblock dateUpdate(\Bitrix\Main\Type\DateTime $value) - set TIMESTAMP_X
 * @method Iblock listPageUrl(string $value) - set LIST_PAGE_URL
 * @method Iblock sectionPageUrl(string $value) - set SECTION_PAGE_URL
 * @method Iblock detailPageUrl(string $value) - set DETAIL_PAGE_URL
 * @method Iblock picture($value) - set PICTURE
 * @method Iblock description($value) - set DESCRIPTION
 * @method Iblock descriptionType($value) - set DESCRIPTION_TYPE
 * @method Iblock version($value) - set VERSION
 * @method Iblock sectionChooser($value) - set SECTION_CHOOSER
 * @package WS\ReduceMigrations\Builder\Entity
 */
class Iblock  extends Base {
    use ContainUserFieldsTrait;

    const SECTION_CHOOSER_LIST = 'L';
    const SECTION_CHOOSER_DROPDOWN = 'D';
    const SECTION_CHOOSER_SEARCH_WINDOW = 'P';

    private $properties = [];
    private $id;
    private $updateProperties = [];
    private $deleteProperties = [];

    const DEFAULT_SORT = 500;

    const IBLOCK_FIRST_VERSION = 1;
    const IBLOCK_SECOND_VERSION = 2;

    public function __construct() {
        $this->dateUpdate(new DateTime());
    }

    /**
     * @param string $iblockType
     * @param string $name
     *
     * @return Iblock
     */
    public static function create($iblockType, $name) {
        $iblock = new self();
        $iblock
            ->name($name)
            ->iblockType($iblockType)
            ->active()
            ->sort(self::DEFAULT_SORT)
            ->version(self::IBLOCK_FIRST_VERSION)
            ->indexElement()
            ->indexSection()
            ->workFlow(false)
            ->sectionChooser(self::SECTION_CHOOSER_LIST);

        return $iblock;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    protected function getMap() {
        return array(
            'name' => 'NAME',
            'code' => 'CODE',
            'iblockType' => 'IBLOCK_TYPE_ID',
            'sort' => 'SORT',
            'siteId' => 'SITE_ID',
            'groupId' => 'GROUP_ID',
            'dateUpdate' => 'TIMESTAMP_X',
            'listPageUrl' => 'LIST_PAGE_URL',
            'sectionPageUrl' => 'SECTION_PAGE_URL',
            'detailPageUrl' => 'DETAIL_PAGE_URL',
            'picture' => 'PICTURE',
            'description' => 'DESCRIPTION',
            'descriptionType' => 'DESCRIPTION_TYPE',
            'version' => 'VERSION',
            'sectionChooser' => 'SECTION_CHOOSER',
        );
    }

    /**
     * @param bool $value
     *
     * @return Iblock
     */
    public function active($value = true) {
        $this->setAttribute('ACTIVE', $value ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return Iblock
     */
    public function indexElement($value = true) {
        $this->setAttribute('INDEX_ELEMENT', $value ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return Iblock
     */
    public function indexSection($value = true) {
        $this->setAttribute('INDEX_SECTION', $value ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return Iblock
     */
    public function workFlow($value = true) {
        $this->setAttribute('WORKFLOW', $value ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param $name
     *
     * @return Property
     */
    public function addProperty($name) {
        $prop = new Property($name);
        $this->properties[$name] = $prop;
        return $prop;
    }

    /**
     * @param $name
     *
     * @return Property
     */
    public function updateProperty($name) {
        $propertyData = $this->findProperty($name);
        $prop = new Property($name, $propertyData);
        $prop->markClean();
        $this->updateProperties[$name] = $prop;
        return $prop;
    }

    /**
     * @param $name
     *
     * @return Property
     */
    public function deleteProperty($name) {
        $propertyData = $this->findProperty($name);
        $prop = new Property($name, $propertyData);
        $this->deleteProperties[$name] = $prop;
        return $prop;
    }

    /**
     * @return Property[]
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * @return Property[]
     */
    public function getUpdateProperties() {
        return $this->updateProperties;
    }

    /**
     * @return Property[]
     */
    public function getDeleteProperties() {
        return $this->deleteProperties;
    }

    /**
     * @param $name
     *
     * @return array|null
     * @throws BuilderException
     */
    private function findProperty($name) {
        $property = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $this->getId(),
            'NAME' => $name
        ))->Fetch();

        if (!$property) {
            throw new BuilderException("Property `$name` not found");
        }
        return $property;
    }

    /**
     * @param $code
     * @return UserField
     */
    public function addSectionField($code) {
        return $this->addUserField($code);
    }

    /**
     * @param $code
     * @return UserField
     * @throws BuilderException
     */
    public function updateSectionField($code) {
        return $this->updateUserField($code, "IBLOCK_{$this->getId()}_SECTION");
    }

    /**
     * @return UserField[]
     */
    public function getSectionFields() {
        return $this->getUserFields();
    }
}
