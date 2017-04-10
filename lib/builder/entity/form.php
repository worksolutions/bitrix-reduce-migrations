<?php

namespace WS\ReduceMigrations\Builder\Entity;

use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class Form
 * @method Form sort(int $value)
 * @method Form name(string $value)
 * @method Form sid(string $value)
 * @method Form useRestrictions(string $value)
 * @method Form restrictUser(int $value)
 * @method Form restrictTime(int $value)
 * @method Form description(string $value)
 * @method Form descriptionType(string $value)
 * @method Form filterResultTemplate(string $value)
 * @method Form tableResultTemplate(string $value)
 * @method Form statEvent1(string $value)
 * @method Form statEvent2(string $value)
 * @method Form statEvent3(string $value)
 * @method Form image(array $value)
 * @method Form arSiteId(array $value)
 * @method Form arMailTemplate(array $value)
 * @method Form arMenu(array $value)
 * @method Form arGroup(array $value)
 * @package WS\ReduceMigrations\Builder\Entity
 */
class Form extends Base {

    private $id;
    private $statuses;
    private $fields;

    public function __construct($name, $sid) {
        $this
            ->name($name)
            ->sid($sid);
    }

    public function getMap() {
        return array(
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
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param bool $useCaptcha
     * @return Form
     */
    public function useCaptcha($useCaptcha) {
        $this->setAttribute('USE_CAPTCHA', $useCaptcha ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param $title
     * @return FormStatus
     * @throws BuilderException
     */
    public function addStatus($title) {

        $status = new FormStatus($title);
        $this->statuses[] = $status;
        return $status;
    }

    /**
     * @param $title
     * @return FormStatus
     * @throws BuilderException
     */
    public function updateStatus($title) {

        $data = $this->findStatus($title);
        $status = new FormStatus($title);
        $status->setId($data['ID']);
        $status->markClean();
        $this->statuses[] = $status;
        return $status;
    }

    /**
     * @param $title
     * @return array
     * @throws BuilderException
     */
    private function findStatus($title) {
        $status = \CFormStatus::GetList($this->getId(), $by, $order, array(
            'TITLE' => $title,
        ), $isFiltered)->Fetch();

        if (empty($status)) {
            throw new BuilderException("Form status '{$title}' not found");
        }
        return $status;
    }

    /**
     * @param $sid
     * @return FormField
     * @throws BuilderException
     */
    public function addField($sid) {
        $field = new FormField($sid);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @param $sid
     * @return FormField
     * @throws BuilderException
     */
    public function updateField($sid) {
        $data = $this->findField($sid);
        $field = new FormField($sid);
        $field->setId($data['ID']);
        $field->markClean();
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @param $sid
     * @return array
     * @throws BuilderException
     */
    private function findField($sid) {
        $field = \CFormField::GetList($this->getId(), 'ALL', $by, $order, array(
            'SID' => $sid,
        ), $isFiltered)->Fetch();
        if (empty($field)) {
            throw new BuilderException("Form field '{$sid}' not found");
        }
        return $field;
    }

    /**
     * @return FormStatus[]
     */
    public function getStatuses() {
        return $this->statuses;
    }

    /**
     * @return FormField[]
     */
    public function getFields() {
        return $this->fields;
    }

}