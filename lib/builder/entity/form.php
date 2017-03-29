<?php
/**
 * Created by PhpStorm.
 * User: under5
 * Date: 10.03.16
 * Time: 11:32
 */

namespace WS\ReduceMigrations\Builder\Entity;

/**
 * Class Form
 * @property int id
 * @property int sort
 * @property string name
 * @property string sid
 * @property string useRestrictions
 * @property string useCaptcha
 * @property int restrictUser
 * @property int restrictTime
 * @property string description
 * @property string descriptionType
 * @property string filterResultTemplate
 * @property string tableResultTemplate
 * @property string statEvent1
 * @property string statEvent2
 * @property string statEvent3
 * @property array image
 * @property array arSiteId
 * @property array arMailTemplate
 * @property array arMenu
 * @property array arGroup
 * @package WS\ReduceMigrations\Builder\Entity
 */
class Form extends Base {

    public function __construct($name, $sid, $data = array()) {
        $this->name = $name;
        $this->sid = $sid;
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'name' => 'NAME',
            'sid' => 'SID',
            'sort' => 'C_SORT',
            'button' => 'BUTTON',
            'useRestrictions' => 'USE_RESTRICTIONS',
            'useCaptcha' => 'USE_CAPTCHA',
            'restrictUser' => 'RESTRICT_USER',
            'restrictTime' => 'RESTRICT_TIME',
            'description' => 'DESCRIPTION',
            'descriptionType' => 'DESCRIPTION_TYPE',
            'filterResultTemplate' => 'FILTER_RESULT_TEMPLATE',
            'tableResultTemplate' => 'TABLE_RESULT_TEMPLATE',
            'statEvent1' => 'STAT_EVENT1',
            'statEvent2' => 'STAT_EVENT2',
            'statEvent3' => 'STAT_EVENT3',
            'image' => 'arIMAGE',
            'arSiteId' => 'arSITE',
            'arMailTemplate' => 'arMAIL_TEMPLATE',
            'arMenu' => 'arMENU',
            'arGroup' => 'arGROUP',
        );
    }

    /**
     * @param int $id
     * @return Form
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $sort
     * @return Form
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
     * @param string $name
     * @return Form
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $sid
     * @return Form
     */
    public function setSid($sid) {
        $this->sid = $sid;
        return $this;
    }

    /**
     * @param string $useRestrictions
     * @return Form
     */
    public function setUseRestrictions($useRestrictions) {
        $this->useRestrictions = $useRestrictions;
        return $this;
    }

    /**
     * @param int $restrictUser
     * @return Form
     */
    public function setRestrictUser($restrictUser) {
        $this->restrictUser = $restrictUser;
        return $this;
    }

    /**
     * @param int $restrictTime
     * @return Form
     */
    public function setRestrictTime($restrictTime) {
        $this->restrictTime = $restrictTime;
        return $this;
    }

    /**
     * @param string $description
     * @return Form
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $descriptionType
     * @return Form
     */
    public function setDescriptionType($descriptionType) {
        $this->descriptionType = $descriptionType;
        return $this;
    }

    /**
     * @param string $filterResultTemplate
     * @return Form
     */
    public function setFilterResultTemplate($filterResultTemplate) {
        $this->filterResultTemplate = $filterResultTemplate;
        return $this;
    }

    /**
     * @param string $tableResultTemplate
     * @return Form
     */
    public function setTableResultTemplate($tableResultTemplate) {
        $this->tableResultTemplate = $tableResultTemplate;
        return $this;
    }

    /**
     * @param string $statEvent1
     * @return Form
     */
    public function setStatEvent1($statEvent1) {
        $this->statEvent1 = $statEvent1;
        return $this;
    }

    /**
     * @param string $statEvent2
     * @return Form
     */
    public function setStatEvent2($statEvent2) {
        $this->statEvent2 = $statEvent2;
        return $this;
    }

    /**
     * @param string $statEvent3
     * @return Form
     */
    public function setStatEvent3($statEvent3) {
        $this->statEvent3 = $statEvent3;
        return $this;
    }

    /**
     * @param array $image
     * @return Form
     */
    public function setImage($image) {
        $this->image = $image;
        return $this;
    }

    /**
     * @param array $arSiteId
     * @return Form
     */
    public function setArSiteId($arSiteId) {
        $this->arSiteId = $arSiteId;
        return $this;
    }

    /**
     * @param array $arMailTemplate
     * @return Form
     */
    public function setArMailTemplate($arMailTemplate) {
        $this->arMailTemplate = $arMailTemplate;
        return $this;
    }

    /**
     * @param array $arMenu
     * @return Form
     */
    public function setArMenu($arMenu) {
        $this->arMenu = $arMenu;
        return $this;
    }

    /**
     * @param array $arGroup
     * @return Form
     */
    public function setArGroup($arGroup) {
        $this->arGroup = $arGroup;
        return $this;
    }

    /**
     * @param bool $useCaptcha
     * @return Form
     */
    public function setUseCaptcha($useCaptcha) {
        $this->useCaptcha = $useCaptcha ? 'Y' : 'N';
        return $this;
    }
}