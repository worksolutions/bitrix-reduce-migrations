<?php

namespace WS\ReduceMigrations\Tests\Cases;

use WS\ReduceMigrations\Builder\Entity\Form;
use WS\ReduceMigrations\Builder\Entity\FormField;
use WS\ReduceMigrations\Builder\FormBuilder;
use WS\ReduceMigrations\Tests\AbstractCase;

class FormBuilderCase extends AbstractCase {

    public function name() {
        return $this->localization->message('name');
    }

    public function description() {
        return $this->localization->message('description');
    }

    public function init() {
        \CModule::IncludeModule('form');
    }

    public function close() {
        $form = \CForm::GetList($by, $order, array(
            'SID' => 'TestForm',
        ), $isFiltered)->Fetch();
        if (!$form) {
            return;
        }
        \CForm::Delete($form['ID'], 'N');
    }

    public function testAdd() {
        $builder = new FormBuilder();
        $newForm = $builder->addForm('TestForm', 'TestForm', function (Form $form) {
              $form
                  ->arSiteId(array('s1'))
                  ->sort(10)
                  ->description('Description')
                  ->useCaptcha(true)
                  ->arGroup(array(
                      '2' => 10
                  ))
                  ->arMenu(array("ru" => "Анкета посетителя", "en" => "Visitor Form"))
                  ->descriptionType('html');

            $form
                ->addField('testQuestion')
                ->fieldType(FormField::FIELD_TYPE_INTEGER)
                ->sort(33)
                ->active(false)
                ->required(true)
                ->title('testTitle')
                ->arFilterAnswerText(array("dropdown"))
                ->arFilterAnswerValue(array("dropdown"))
                ->arFilterUser(array("dropdown"))
                ->arFilterField(array("integer"))
                ->comments('test comment')
                ->addAnswer('Привет мир!');

            $form
                ->addField('testField')
                ->asField()
                ->title('test')
            ;

            $form
                ->addStatus('status')
                ->arGroupCanDelete(array(2))
                ->byDefault(true);
        });


        $form = \CForm::GetList($by, $order, array(
            'ID' => $newForm->getId(),
        ), $isFiltered)->Fetch();

        $this->assertNotEmpty($form);
        $this->assertEquals($form['C_SORT'], 10);
        $this->assertNotEmpty($form['DESCRIPTION'], 'Description');
        $this->assertNotEmpty($form['NAME'], 'TestForm');
        $this->assertNotEmpty($form['USE_CAPTCHA'], 'Y');

        $res = \CFormField::GetList($newForm->getId(), 'ALL', $by, $order, array(), $isFiltered);

        $this->assertEquals($res->SelectedRowsCount(), 2);
        while ($item = $res->fetch()) {
            if ($item['SID'] == 'testQuestion') {
                $this->assertEquals($item['ACTIVE'], 'N');
                $this->assertEquals($item['ADDITIONAL'], 'N');
                $this->assertEquals($item['FIELD_TYPE'], 'integer');
                $this->assertEquals($item['TITLE'], 'testTitle');
                $this->assertEquals($item['C_SORT'], 33);
                $this->assertEquals($item['REQUIRED'], 'Y');
                $this->assertEquals($item['COMMENTS'], 'test comment');
            }
            if ($item['SID'] == 'testField') {
                $this->assertEquals($item['ADDITIONAL'], 'Y');
                $this->assertEquals($item['TITLE'], 'test');
            }
        }

        $res = \CFormStatus::GetList($newForm->getId(), $by, $order, array(), $isFiltered)->Fetch();
        $this->assertEquals($res['TITLE'], 'status');
        $this->assertEquals($res['DEFAULT_VALUE'], 'Y');
    }


    public function testUpdate() {
        $builder = new FormBuilder();
        $updatedForm = $builder->updateForm('TestForm', function (Form $form) {
            $form->name('MyTestForm');
            $field = $form
                ->updateField('testQuestion')
                ->active(true)
                ->required(false);

            $field->removeAnswer('Привет мир!');

            $field
                ->addAnswer('Test')
                ->value('val1');

            $form
                ->updateStatus('status')
                ->description('test22')
                ->arGroupCanDelete(array(2, 3));
        });

        $form = \CForm::GetList($by, $order, array(
            'ID' => $updatedForm->getId(),
        ), $isFiltered)->Fetch();

        $this->assertNotEmpty($form);
        $this->assertNotEmpty($form['NAME'], 'MyTestForm');

        $res = \CFormField::GetList($updatedForm->getId(), 'ALL', $by, $order, array(), $isFiltered);

        $this->assertEquals($res->SelectedRowsCount(), 2);
        while ($item = $res->fetch()) {
            if ($item['SID'] == 'testQuestion') {
                $this->assertEquals($item['ACTIVE'], 'Y');
                $this->assertEquals($item['ADDITIONAL'], 'N');
                $this->assertEquals($item['FIELD_TYPE'], 'integer');
                $this->assertEquals($item['TITLE'], 'testTitle');
                $this->assertEquals($item['REQUIRED'], 'N');
                $this->assertEquals($item['COMMENTS'], 'test comment');
            }
        }

        $res = \CFormStatus::GetList($updatedForm->getId(), $by, $order, array(), $isFiltered)->Fetch();
        $this->assertEquals($res['DESCRIPTION'], 'test22');
    }

}