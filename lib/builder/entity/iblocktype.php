<?php
/**
 * Created by PhpStorm.
 * User: under5
 * Date: 10.03.16
 * Time: 11:32
 */

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class IblockType
 * @property int id
 * @property int sort
 * @property string sections
 * @property string lang
 * @property string inRss
 * @property string iblockTypeId
 * @package WS\ReduceMigrations\Builder\Entity
 */
class IblockType extends Base {

    public function __construct($type, $data = array()) {
        $this->id = $type;
        $this->iblockTypeId = $data['ID'];
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'sort' => 'SORT',
            'sections' => 'SECTIONS',
            'inRss' => 'IN_RSS',
            'lang' => 'LANG',
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
     * @param int $sort
     * @return IblockType
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $sections
     * @return IblockType
     */
    public function setSections($sections) {
        $this->sections = $sections;
        return $this;
    }

    /**
     * @param array $lang ['en' => ['NAME'=>'Catalog', 'SECTION_NAME'=>'Sections', 'ELEMENT_NAME'=>'Products']]
     * @return IblockType
     */
    public function setLang($lang) {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @param bool $inRss
     * @return IblockType
     */
    public function setInRss($inRss) {
        $this->inRss = $inRss ? 'Y' : 'N';
        return $this;
    }
}