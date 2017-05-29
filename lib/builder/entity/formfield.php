<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class FormField
 * 
 * @method FormField sort(int $value)
 * @method FormField formId(int $value)
 * @method FormField sid(string $value)
 * @method FormField title(string $value)
 * @method FormField titleType(string $value)
 * @method FormField additional(string $value)
 * @method FormField fieldType(string $value)
 * @method FormField filterTitle(string $value)
 * @method FormField resultsTableTitle(string $value)
 * @method FormField comments(string $value)
 * @method FormField arImage(array $value)
 * @method FormField arFilterUser(array $value)
 * @method FormField arFilterAnswerText(array $value)
 * @method FormField arFilterAnswerValue(array $value)
 * @method FormField arFilterField(array $value)
 * @package WS\ReduceMigrations\Builder\Entity
 */
class FormField extends Base {

    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_INTEGER = 'integer';
    const FIELD_TYPE_DATE = 'date';
    /** @var  FormAnswer[] */
    private $answers;
    private $id;


    public function __construct($sid) {
        $this->setAttribute('SID', $sid);
        $this->answers = array();
    }

    public function getMap() {
        return array(
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
     * @param bool $active
     * @return FormField
     */
    public function active($active) {
        $this->setAttribute('ACTIVE', $active ? 'Y' : 'N');
        return $this;
    }

    /**
     * @return FormField
     */
    public function asQuestion() {
        $this->setAttribute('ADDITIONAL', 'N');
        return $this;
    }

    /**
     * @return FormField
     */
    public function asField() {
        $this->setAttribute('ADDITIONAL', 'Y');
        return $this;
    }

    /**
     * @param bool $required
     * @return FormField
     */
    public function required($required = true) {
        $this->setAttribute('REQUIRED', $required ? "Y" : "N");
        return $this;
    }

    /**
     * @param bool $inResultsTable
     * @return FormField
     */
    public function inResultsTable($inResultsTable) {
        $this->setAttribute('IN_RESULTS_TABLE', $inResultsTable ? "Y" : "N");
        return $this;
    }

    /**
     * @param bool $inExcelTable
     * @return FormField
     */
    public function inExcelTable($inExcelTable) {
        $this->setAttribute('IN_EXCEL_TABLE', $inExcelTable ? "Y" : "N");
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
        $data = $this->findAnswer($message);
        $answer = new FormAnswer($message);
        $answer->setId($data['ID']);
        $answer->markClean();
        $this->answers[] = $answer;
        return $answer;
    }

    /**
     * @param $message
     *
     * @return FormAnswer
     * @throws BuilderException
     */
    public function removeAnswer($message) {
        $data = $this->findAnswer($message);
        $answer = new FormAnswer($message);
        $answer->markDelete();
        $answer->setId($data['ID']);
        $this->answers[] = $answer;
        return $answer;
    }

    private function findAnswer($message) {
        $data = \CFormAnswer::GetList($this->getId(), $by = null, $order = null, array(
            'MESSAGE' => $message
        ), $isFiltered = false)->Fetch();

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