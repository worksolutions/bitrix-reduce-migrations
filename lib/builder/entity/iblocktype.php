<?php

namespace WS\ReduceMigrations\Builder\Entity;


class IblockType extends Base {

    public function __construct($type) {
        $this->setId($type);
        $this->type($type);
    }

    /**
     * @param string $id
     * @return IblockType
     */
    public function setId($id) {
        $this->setAttribute('ID', $id);
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->getAttribute('ID');
    }

    /**
     * @param string $value
     * @return IblockType
     */
    public function type($value) {
        $this->setAttribute('IBLOCK_TYPE_ID', $value);
        return $this;
    }

    /**
     * @param int $sort
     * @return IblockType
     */
    public function sort($sort) {
        $this->setAttribute('SORT', $sort);
        return $this;
    }

    /**
     * @param string $sections
     * @return IblockType
     */
    public function sections($sections) {
        $this->setAttribute('SECTIONS', $sections);
        return $this;
    }

    /**
     * @param array $lang ['en' => ['NAME'=>'Catalog', 'SECTION_NAME'=>'Sections', 'ELEMENT_NAME'=>'Products']]
     * @return IblockType
     */
    public function lang($lang) {
        $this->setAttribute('LANG', $lang);
        return $this;
    }

    /**
     * @param bool $inRss
     * @return IblockType
     */
    public function inRss($inRss) {
        $this->setAttribute('IN_RSS', $inRss ? 'Y' : 'N');
        return $this;
    }
}