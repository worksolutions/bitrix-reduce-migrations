<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class Iblock
 * @property int id
 * @property string name
 * @property string code
 * @property string iblockTypeId
 * @property int sort
 * @property string siteId
 * @property string active
 * @property string listPageUrl
 * @property string sectionPageUrl
 * @property string detailPageUrl
 * @property string description
 * @property string descriptionType
 * @property string rssActive
 * @property int rssTtl
 * @property string rssFileActive
 * @property int rssFileLimit
 * @property int rssFileDays
 * @property string rssYandexActive
 * @property string indexElement
 * @property string indexSection
 * @property string workFlow
 * @property string sectionChooser (D|L|P)
 * @property int version
 * @property array picture
 * @property dateTime dateUpdate
 * @property array groupId
 * @package WS\ReduceMigrations\Builder\Entity
 */
class Iblock extends Base {
    const SECTION_CHOOSER_LIST = 'L';
    const SECTION_CHOOSER_DROPDOWN = 'D';
    const SECTION_CHOOSER_SEARCH_WINDOW = 'P';

    public function __construct($name, $data = false) {
        $this->name = $name;
        $this->setSaveData($data);
        $this->dateUpdate = new DateTime();
    }

    /**
     * @return array
     */
    public function getMap() {
        return array(
            'id' => 'ID',
            'name' => 'NAME',
            'code' => 'CODE',
            'iblockTypeId' => 'IBLOCK_TYPE_ID',
            'sort' => 'SORT',
            'siteId' => 'SITE_ID',
            'groupId' => 'GROUP_ID',
            'dateUpdate' => 'TIMESTAMP_X',
            'active' => 'ACTIVE',
            'listPageUrl' => 'LIST_PAGE_URL',
            'sectionPageUrl' => 'SECTION_PAGE_URL',
            'detailPageUrl' => 'DETAIL_PAGE_URL',
            'picture' => 'PICTURE',
            'description' => 'DESCRIPTION',
            'descriptionType' => 'DESCRIPTION_TYPE',
            'rssActive' => 'RSS_ACTIVE',
            'rssTtl' => 'RSS_TTL',
            'rssFileActive' => 'RSS_FILE_ACTIVE',
            'rssFileLimit' => 'RSS_FILE_LIMIT',
            'rssFileDays' => 'RSS_FILE_DAYS',
            'rssYandexActive' => 'RSS_YANDEX_ACTIVE',
            'indexElement' => 'INDEX_ELEMENT',
            'indexSection' => 'INDEX_SECTION',
            'workFlow' => 'WORKFLOW',
            'sectionChooser' => 'SECTION_CHOOSER',
            'version' => 'VERSION',
        );
    }

    /**
     * @param string $name
     * @return Iblock
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $code
     * @return Iblock
     */
    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    /**
     * @param string $iblockTypeId
     * @return Iblock
     */
    public function setIblockTypeId($iblockTypeId) {
        $this->iblockTypeId = $iblockTypeId;
        return $this;
    }

    /**
     * @param int $sort
     * @return Iblock
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param string $siteId
     * @return Iblock
     */
    public function setSiteId($siteId) {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * @param array $groupId
     * @return Iblock
     */
    public function setGroupId($groupId) {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @param bool $active
     * @return Iblock
     */
    public function setActive($active = true) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $listPageUrl
     * @return Iblock
     */
    public function setListPageUrl($listPageUrl) {
        $this->listPageUrl = $listPageUrl;
        return $this;
    }

    /**
     * @param string $sectionPageUrl
     * @return Iblock
     */
    public function setSectionPageUrl($sectionPageUrl) {
        $this->sectionPageUrl = $sectionPageUrl;
        return $this;
    }

    /**
     * @param string $detailPageUrl
     * @return Iblock
     */
    public function setDetailPageUrl($detailPageUrl) {
        $this->detailPageUrl = $detailPageUrl;
        return $this;
    }

    /**
     * @param string $description
     * @return Iblock
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $descriptionType
     * @return Iblock
     */
    public function setDescriptionType($descriptionType) {
        $this->descriptionType = $descriptionType;
        return $this;
    }

    /**
     * @param bool $rssActive
     * @return Iblock
     */
    public function setRssActive($rssActive = true) {
        $this->rssActive = $rssActive ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param int $rssTtl
     * @return Iblock
     */
    public function setRssTtl($rssTtl) {
        $this->rssTtl = $rssTtl;
        return $this;
    }

    /**
     * @param bool $rssFileActive
     * @return Iblock
     */
    public function setRssFileActive($rssFileActive = true) {
        $this->rssFileActive = $rssFileActive ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param int $rssFileLimit
     * @return Iblock
     */
    public function setRssFileLimit($rssFileLimit) {
        $this->rssFileLimit = $rssFileLimit;
        return $this;
    }

    /**
     * @param int $rssFileDays
     * @return Iblock
     */
    public function setRssFileDays($rssFileDays) {
        $this->rssFileDays = $rssFileDays;
        return $this;
    }

    /**
     * @param bool $rssYandexActive
     * @return Iblock
     */
    public function setRssYandexActive($rssYandexActive = true) {
        $this->rssYandexActive = $rssYandexActive ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $indexElement
     * @return Iblock
     */
    public function setIndexElement($indexElement = true) {
        $this->indexElement = $indexElement ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $indexSection
     * @return Iblock
     */
    public function setIndexSection($indexSection = true) {
        $this->indexSection = $indexSection ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $workFlow
     * @return Iblock
     */
    public function setWorkFlow($workFlow = true) {
        $this->workFlow = $workFlow ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param string $sectionChooser (D|L|P)
     * @return Iblock
     */
    public function setSectionChooser($sectionChooser) {
        $this->sectionChooser = $sectionChooser;
        return $this;
    }

    /**
     * @param int $version 1|2
     * @return Iblock
     */
    public function setVersion($version) {
        $this->version = $version;
        return $this;
    }

    /**
     * @param array $picture
     * @return Iblock
     */
    public function setPicture($picture) {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @param int $id
     * @return Iblock
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

}
