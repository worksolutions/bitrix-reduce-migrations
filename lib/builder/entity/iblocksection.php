<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class IblockSection
 * @property int id
 * @property int sort
 * @property string code
 * @property string xmlId
 * @property string name
 * @property string active
 * @property string description
 * @property string descriptionType
 * @property array picture
 * @property array detailPicture
 * @property int iblockId
 * @package WS\ReduceMigrations\Builder\Entity
 */
class IblockSection extends Base {

    /**
     * @var IblockSection[]
     */
    private $children;

    public function __construct($name, $data = array()) {
        $this->name = $name;
        $this->childs = array();
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'iblockId' => 'IBLOCK_ID',
            'code' => 'CODE',
            'xmlId' => 'XML_ID',
            'sort' => 'SORT',
            'name' => 'NAME',
            'active' => 'ACTIVE',
            'picture' => 'PICTURE',
            'description' => 'DESCRIPTION',
            'descriptionType' => 'DESCRIPTION_TYPE',
            'detailPicture' => 'DETAIL_PICTURE',
        );
    }

    /**
     * @param int $id
     * @return EventType
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
     * @return EventType
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param string $eventName
     * @return EventType
     */
    public function setEventName($eventName) {
        $this->eventName = $eventName;
        return $this;
    }

    /**
     * @param string $lid
     * @return EventType
     */
    public function setLid($lid) {
        $this->lid = $lid;
        return $this;
    }

    /**
     * @param string $name
     * @return EventType
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $description
     * @return EventType
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param $name
     * @return IblockSection
     */
    public function getChild($name) {
        return $this->findChild($name);
    }

    /**
     * @param $name
     * @param bool $data
     * @return IblockSection
     */
    public function addChild($name, $data = false) {
        $child = new IblockSection($name, $data);
        $this->children[] = $child;
        return $child;
    }

    private function findChild($name) {
        foreach ($this->children as $child) {
            if ($child->name != $name) {
                continue;
            }
            return $child;
        }
        $child = $this->getChildFromDB($name);
        $this->children[] = $child;
        return $child;
    }
    /**
     * @return IblockSection[]
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @param $name
     * @return IblockSection
     * @throws BuilderException
     */
    private function getChildFromDB($name) {
        if (!$this->id) {
            throw new BuilderException("Save section for continue");
        }
        $item = \CIBlockSection::GetList(null, array(
            'IBLOCK_ID' => $this->iblockId,
            'SECTION_ID' => $this->id,
            '=NAME' => $name
        ))->Fetch();
        if (empty($item)) {
            throw new BuilderException("Iblock section with name '{$name}' not found");
        }
        return new IblockSection($name, $item);
    }
}