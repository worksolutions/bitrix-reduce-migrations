<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Builder\BuilderException;

class Iblock  extends Base {
    const SECTION_CHOOSER_LIST = 'L';
    const SECTION_CHOOSER_DROPDOWN = 'D';
    const SECTION_CHOOSER_SEARCH_WINDOW = 'P';

    private $properties;
    private $id;
    private $updateProperties;
    private $deleteProperties;

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
            ->sort(500)
            ->version(1)
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

    /**
     * @param string $name
     *
     * @return Iblock
     */
    public function name($name) {
        $this->setAttribute('NAME', $name);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return Iblock
     */
    public function code($value) {
        $this->setAttribute('CODE', $value);
        return $this;
    }

    /**
     * @param integer $value
     *
     * @return Iblock
     */
    public function iblockType($value) {
        $this->setAttribute('IBLOCK_TYPE_ID', $value);
        return $this;
    }

    /**
     * @param integer $value
     *
     * @return Iblock
     */
    public function sort($value) {
        $this->setAttribute('SORT', $value);
        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return Iblock
     */
    public function siteId($value) {
        $this->setAttribute('SITE_ID', $value);
        return $this;
    }

    /**
     * @param array $value
     *
     * @return Iblock
     */
    public function groupId($value) {
        $this->setAttribute('GROUP_ID', $value);
        return $this;
    }

    /**
     * @param DateTime $value
     *
     * @return Iblock
     */
    public function dateUpdate($value) {
        $this->setAttribute('TIMESTAMP_X', $value);
        return $this;
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
     * @param string $value
     *
     * @return Iblock
     */
    public function listPageUrl($value) {
        $this->setAttribute('LIST_PAGE_URL', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return Iblock
     */
    public function sectionPageUrl($value) {
        $this->setAttribute('SECTION_PAGE_URL', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return Iblock
     */
    public function detailPageUrl($value) {
        $this->setAttribute('DETAIL_PAGE_URL', $value);
        return $this;
    }

    /**
     * @param $value
     *
     * @return Iblock
     */
    public function picture($value) {
        $this->setAttribute('PICTURE', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return Iblock
     */
    public function description($value) {
        $this->setAttribute('DESCRIPTION', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return Iblock
     */
    public function descriptionType($value) {
        $this->setAttribute('DESCRIPTION_TYPE', $value);
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
     * @param integer $value
     *
     * @return Iblock
     */
    public function version($value = 1) {
        $this->setAttribute('VERSION', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return Iblock
     */
    public function sectionChooser($value) {
        $this->setAttribute('SECTION_CHOOSER', $value);
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

}
