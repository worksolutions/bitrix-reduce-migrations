<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\Form;
use WS\ReduceMigrations\Builder\Entity\FormField;

class FormBuilder {

    public function __construct() {
        \CModule::IncludeModule('form');
    }

    /**
     * @param string $name
     * @param string $sid
     * @param \Closure $callback
     *
     * @return Form
     * @throws BuilderException
     */
    public function addForm($name, $sid, $callback) {

        $form = new Form($name, $sid);
        $callback($form);
        $this->commit($form);
        return $form;
    }

    /**
     * @param string $sid
     * @param \Closure $callback
     * @return Form
     * @throws BuilderException
     */
    public function updateForm($sid, $callback) {
        $formData = $this->findForm($sid);
        $form = new Form($formData['NAME'], $sid);
        $form->setId($formData['ID']);
        $form->markClean();
        $callback($form);
        $this->commit($form);
        return $form;
    }

    /**
     * @param string $sid
     * @return boolean
     */
    public function removeForm($sid) {
        $formData = $this->findForm($sid);
        if (!$formData['ID']) {
            return false;
        }
        return \CForm::Delete($formData['ID']);
    }

    /**
     * @param Form $form
     * @throws BuilderException
     */
    protected function commit($form) {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitForm($form);
            $this->commitFields($form);
            $this->commitStatuses($form);
        } catch (\Exception $e) {
            $DB->Rollback();
            throw new BuilderException($e->getMessage());
        }
        $DB->Commit();
    }

    /**
     * @param Form $form
     * @throws BuilderException
     */
    private function commitForm($form) {
        global $strError;
        if (!$form->isDirty()) {
           return ;
        }
        $formId = \CForm::Set($form->getData(), $form->getId(), 'N');
        if (!$formId) {
            throw new BuilderException("Form wasn't saved. " . $strError);
        }
        $form->setId($formId);
    }

    /**
     * @param Form $form
     * @throws BuilderException
     */
    private function commitFields($form) {
        global $strError;
        $gw = new \CFormField();
        foreach ($form->getFields() as $field) {
            if ($field->isDirty()) {
                $field->setAttribute('FORM_ID', $form->getId());
                $saveData = $field->getData();
                $fieldId = $gw->Set($saveData, $field->getId(), 'N', 'Y');
                if (!$fieldId) {
                    throw new BuilderException("Field '{$field->getAttribute('SID')}' wasn't saved. " . $strError);
                }
                $field->setId($fieldId);
            }

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
                    throw new BuilderException("Can't delete '{$answer->getAttribute('MESSAGE')}'. ". $strError);
                }
            }
            $answer->setAttribute('QUESTION_ID', $field->getId());
            $data = $answer->getData();
            if ($answer->isDirty() && !$gw->Set($data, $answer->getId())) {
                throw new BuilderException("Answer wasn't saved. " . $strError);
            }
        }
    }

    /**
     * @param Form $form
     * @throws BuilderException
     */
    private function commitStatuses($form) {
        global $strError;
        $gw = new \CFormStatus();
        foreach ($form->getStatuses() as $status) {
            if (!$form->isDirty()) {
                continue;
            }
            $status->setAttribute('FORM_ID', $form->getId());
            $saveData = $status->getData();
            $statusId = $gw->Set($saveData, $status->getId(), 'N');
            if (!$statusId) {
                throw new BuilderException("Field '{$status->getAttribute('TITLE')}' wasn't saved. " . $strError);
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
        ), $isFiltered = false)->Fetch();

        if (!$data) {
            throw new BuilderException("Form '{$sid}' not found");
        }

        return $data;
    }

}
