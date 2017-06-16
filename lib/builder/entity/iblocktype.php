<?php

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class IblockType
 *
 * @method IblockType type(string $value) - IBLOCK_TYPE_ID
 * @method IblockType sort(int $value) - SORT
 * @method IblockType sections(string $value) - SECTIONS
 * @method IblockType lang(array $value) - LANG ['en' => ['NAME'=>'Catalog', 'SECTION_NAME'=>'Sections', 'ELEMENT_NAME'=>'Products']]
 * @package WS\ReduceMigrations\Builder\Entity
 */
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
     * @return string
     */
    public function getId() {
        return $this->getAttribute('ID');
    }

    protected function getMap() {
        return array(
            'type' => 'IBLOCK_TYPE_ID',
            'sort' => 'SORT',
            'sections' => 'SECTIONS',
            'lang' => 'LANG',
        );
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
