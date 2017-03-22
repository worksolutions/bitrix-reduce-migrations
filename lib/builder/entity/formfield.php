<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class FormField
 * @property int id
 * @property int sort
 * @property int formId
 * @property string sid
 * @property string title
 * @property string titleType
 * @property string active
 * @property string additional
 * @property string fieldType
 * @property string required
 * @property string filterTitle
 * @property string inResultsTable
 * @property string inExcelTable
 * @property string resultsTableTitle
 * @property string comments
 * @property array arImage
 * @property array arFilterUser
 * @property array arFilterAnswerText
 * @property array arFilterAnswerValue
 * @property array arFilterField
 * @package WS\Migrations\Builder\Entity
 */
class FormField extends Base {

    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_INTEGER = 'integer';
    const FIELD_TYPE_DATE = 'date';
    /** @var  FormAnswer[] */
    private $answers;


    public function __construct($sid, $data = array()) {
        $this->sid = $sid;
        $this->answers = array();
        $this->setSaveData($data);
    }

    public function getMap() {
        return array(
            'id' => 'ID',
            'sort' => 'C_SORT',
            'sid' => 'SID',
            'formId' => 'FORM_ID',
            'active' => 'ACTIVE',
            'additional' => 'ADDITIONAL',
            'fieldType' => 'FIELD_TYPE',
            'title' => 'TITLE',
            'titleType' => 'TITLE_TYPE',
            'required' => 'REQUIRED',
            'filterTitle' => 'FILTER_TITLE',
            'inResultsTable' => 'IN_RESULTS_TABLE',
            'inExcelTable' => 'IN_EXCEL_TABLE',
            'resultsTableTitle' => 'RESULTS_TABLE_TITLE',
            'comments' => 'COMMENTS',
            'arImage' => 'arIMAGE',
            'arFilterUser' => 'arFILTER_USER',
            'arFilterAnswerText' => 'arFILTER_ANSWER_TEXT',
            'arFilterAnswerValue' => 'arFILTER_ANSWER_VALUE',
            'arFilterField' => 'arFILTER_FIELD',
        );
    }

    /**
     * @param int $id
     * @return FormField
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
     * @return FormField
     */
    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param string $sid
     * @return FormField
     */
    public function setSid($sid) {
        $this->sid = $sid;
        return $this;
    }

    /**
     * @param string $title
     * @return FormField
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $titleType
     * @return FormField
     */
    public function setTitleType($titleType) {
        $this->titleType = $titleType;
        return $this;
    }

    /**
     * @param bool $active
     * @return FormField
     */
    public function setActive($active) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @return FormField
     */
    public function setAsQuestion() {
        $this->additional = 'N';
        return $this;
    }

    /**
     * @return FormField
     */
    public function setAsField() {
        $this->additional = 'Y';
        return $this;
    }

    /**
     * @param string $fieldType
     * @return FormField
     */
    public function setFieldType($fieldType) {
        $this->fieldType = $fieldType;
        return $this;
    }

    /**
     * @param bool $required
     * @return FormField
     */
    public function setRequired($required) {
        $this->required = $required ? "Y" : "N";
        return $this;
    }

    /**
     * @param string $filterTitle
     * @return FormField
     */
    public function setFilterTitle($filterTitle) {
        $this->filterTitle = $filterTitle;
        return $this;
    }

    /**
     * @param bool $inResultsTable
     * @return FormField
     */
    public function setInResultsTable($inResultsTable) {
        $this->inResultsTable = $inResultsTable ? "Y" : "N";;
        return $this;
    }

    /**
     * @param bool $inExcelTable
     * @return FormField
     */
    public function setInExcelTable($inExcelTable) {
        $this->inExcelTable = $inExcelTable ? "Y" : "N";;
        return $this;
    }

    /**
     * @param string $resultsTableTitle
     * @return FormField
     */
    public function setResultsTableTitle($resultsTableTitle) {
        $this->resultsTableTitle = $resultsTableTitle;
        return $this;
    }

    /**
     * @param string $comments
     * @return FormField
     */
    public function setComments($comments) {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @param array $arImage
     * @return FormField
     */
    public function setArImage($arImage) {
        $this->arImage = $arImage;
        return $this;
    }

    /**
     * @param array $arFilterUser
     * @return FormField
     */
    public function setArFilterUser($arFilterUser) {
        $this->arFilterUser = $arFilterUser;
        return $this;
    }

    /**
     * @param array $arFilterAnswerText
     * @return FormField
     */
    public function setArFilterAnswerText($arFilterAnswerText) {
        $this->arFilterAnswerText = $arFilterAnswerText;
        return $this;
    }

    /**
     * @param array $arFilterAnswerValue
     * @return FormField
     */
    public function setArFilterAnswerValue($arFilterAnswerValue) {
        $this->arFilterAnswerValue = $arFilterAnswerValue;
        return $this;
    }

    /**
     * @param array $arFilterField
     * @return FormField
     */
    public function setArFilterField($arFilterField) {
        $this->arFilterField = $arFilterField;
        return $this;
    }

    /**
     * @param $message
     * @return FormAnswer
     */
    public function addAnswer($message) {
        $answer = new FormAnswer($message);
        $this->answers[] = $answer;
        return $answer;
    }

    /**
     * @param $message
     * @return FormAnswer
     * @throws BuilderException
     */
    public function updateAnswer($message) {
        if (!$this->id) {
            throw new BuilderException("Can't update answer. Form Field not saved");
        }
        $data = $this->findAnswer($message);
        $answer = new FormAnswer($message, $data);
        $this->answers[] = $answer;
        return $answer;
    }

    public function removeAnswer($message) {
        if (!$this->id) {
            throw new BuilderException("Can't remove answer. Form Field not saved");
        }
        $data = $this->findAnswer($message);
        $data['DEL'] = 'Y';
        $answer = new FormAnswer($message, $data);
        $this->answers[] = $answer;
        return $answer;
    }

    private function findAnswer($message) {
        $data = \CFormAnswer::GetList($this->id, $by, $order, array(
            'MESSAGE' => $message
        ), $isFiltered)->Fetch();

        if (empty($data)) {
            throw new BuilderException("Answer '{$message}' not found");
        }
        return $data;
    }

    /**
     * @return FormAnswer[]
     */
    public function getAnswers() {
        return $this->answers;
    }

}