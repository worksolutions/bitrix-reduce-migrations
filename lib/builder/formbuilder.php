<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\Form;
use WS\ReduceMigrations\Builder\Entity\FormField;
use WS\ReduceMigrations\Builder\Entity\FormStatus;

class FormBuilder {
    /** @var  Form */
    private $form;
    /** @var  FormField[] */
    private $fields;
    /** @var  FormStatus[] */
    private $statuses;

    public function __construct() {
        \CModule::IncludeModule('form');
    }

    public function reset() {
        $this->form = null;
        $this->fields = array();
        $this->statuses = array();
    }

    /**
     * @param $name
     * @param $sid
     * @return Form
     * @throws BuilderException
     */
    public function addForm($name, $sid) {
        if ($this->form) {
            throw new BuilderException('Form already set');
        }
        $this->form = new Form($name, $sid);
        return $this->form;
    }

    /**
     * @param $sid
     * @return Form
     * @throws BuilderException
     */
    public function getForm($sid) {
        if ($this->form) {
            throw new BuilderException('Form already set');
        }
        $formData = $this->findForm($sid);
        $this->form = new Form($formData['NAME'], $sid, $formData);
        return $this->form;
    }

    /**
     * @param $title
     * @return FormStatus
     * @throws BuilderException
     */
    public function addStatus($title) {
        if (!$this->form) {
            throw new BuilderException("Form doesn't set");
        }
        $status = new FormStatus($title);
        $this->statuses[] = $status;
        return $status;
    }

    /**
     * @param $title
     * @return FormStatus
     * @throws BuilderException
     */
    public function getStatus($title) {
        if (!$this->form) {
            throw new BuilderException("Form doesn't set");
        }
        $data = $this->findStatus($title);
        $status = new FormStatus($title, $data);
        $this->statuses[] = $status;
        return $status;
    }

    /**
     * @param $sid
     * @return FormField
     * @throws BuilderException
     */
    public function addField($sid) {
        if (!$this->form) {
            throw new BuilderException("Form doesn't set");
        }
        $field = new FormField($sid);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @param $sid
     * @return FormField
     * @throws BuilderException
     */
    public function getField($sid) {
        if (!$this->form) {
            throw new BuilderException("Form doesn't set");
        }
        $data = $this->findField($sid);
        $field = new FormField($sid, $data);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @throws BuilderException
     */
    public function commit() {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitForm();
            $this->commitFields();
            $this->commitStatuses();
        } catch (\Exception $e) {
            $DB->Rollback();
            throw new BuilderException($e->getMessage());
        }
        $DB->Commit();
    }

    /**
     * @throws BuilderException
     */
    private function commitForm() {
        global $strError;
        $formId = \CForm::Set($this->form->getSaveData(), $this->form->getId(), 'N');
        if (!$formId) {
            throw new BuilderException("Form wasn't saved. " . $strError);
        }
        $this->form->setId($formId);
    }

    /**
     * @throws BuilderException
     */
    private function commitFields() {
        global $strError;
        $gw = new \CFormField();
        foreach ($this->fields as $field) {
            $saveData = $field->getSaveData();
            $saveData['FORM_ID'] = $this->form->getId();
            $fieldId = $gw->Set($saveData, $field->getId(), 'N', 'Y');
            if (!$fieldId) {
                throw new BuilderException("Field '{$field->sid}' wasn't saved. " . $strError);
            }
            $field->setId($fieldId);
            $this->commitAnswers($field);
        }
    }

    /**
     * @param FormField $field
     * @throws BuilderException
     */
    private function commitAnswers($field) {
        global $strError;
        $gw = new \CFormAnswer();
        foreach ($field->getAnswers() as $answer) {
            if ($answer->needDelete()) {
                if (!$gw->Delete($answer->getId())) {
                    throw new BuilderException("Can't delete '{$answer->message}'. ". $strError);
                }
            }
            $data = $answer->getSaveData();
            $data['QUESTION_ID'] = $field->getId();
            if (!$gw->Set($data, $answer->getId())) {
                throw new BuilderException("Answer wasn't saved. " . $strError);
            }
        }
    }

    /**
     * @throws BuilderException
     */
    private function commitStatuses() {
        global $strError;
        $gw = new \CFormStatus();
        foreach ($this->statuses as $status) {
            $saveData = $status->getSaveData();
            $saveData['FORM_ID'] = $this->form->getId();
            $statusId = $gw->Set($saveData, $status->getId(), 'N');
            if (!$statusId) {
                throw new BuilderException("Field '{$status->title}' wasn't saved. " . $strError);
            }
            $status->setId($statusId);
        }
    }

    /**
     * @param $sid
     * @return array
     * @throws BuilderException
     */
    private function findForm($sid) {
        $data = \CForm::GetList($by = 'ID', $order = 'ASC', array(
            'SID' => $sid
        ), $isFiltered)->Fetch();

        if (!$data) {
            throw new BuilderException("Form '{$sid}' not found");
        }

        return $data;
    }

    /**
     * @param $sid
     * @return array
     * @throws BuilderException
     */
    private function findField($sid) {
        if (!$this->form->getId()) {
            throw new BuilderException("Form doesn't set");
        }
        $field = \CFormField::GetList($this->form->getId(), 'ALL', $by, $order, array(
            'SID' => $sid,
        ), $isFiltered)->Fetch();
        if (empty($field)) {
            throw new BuilderException("Form field '{$sid}' not found");
        }
        return $field;
    }

    /**
     * @param $title
     * @return array
     * @throws BuilderException
     */
    private function findStatus($title) {
        if (!$this->form->getId()) {
            throw new BuilderException("Form doesn't set");
        }
        $status = \CFormStatus::GetList($this->form->getId(), $by, $order, array(
            'TITLE' => $title,
        ), $isFiltered)->Fetch();

        if (empty($status)) {
            throw new BuilderException("Form status '{$title}' not found");
        }
        return $status;
    }

    /**
     * @return Form
     */
    public function getCurrentForm() {
        return $this->form;
    }

}
