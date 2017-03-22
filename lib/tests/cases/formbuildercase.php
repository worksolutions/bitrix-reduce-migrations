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
        $builder
            ->addForm('TestForm', 'TestForm')
            ->setArSiteId(array('s1'))
            ->setSort('10')
            ->setDescription('Description')
            ->setUseCaptcha(true)
            ->setArGroup(array(
                '2' => 10
            ))
            ->setArMenu(array("ru" => "Анкета посетителя", "en" => "Visitor Form"))
            ->setDescriptionType('html')
        ;

        $builder
            ->addField('testQuestion')
            ->setFieldType(FormField::FIELD_TYPE_INTEGER)
            ->setSort(33)
            ->setActive(false)
            ->setRequired(true)
            ->setTitle('testTitle')
            ->setArFilterAnswerText(array("dropdown"))
            ->setArFilterAnswerValue(array("dropdown"))
            ->setArFilterUser(array("dropdown"))
            ->setArFilterField(array("integer"))
            ->setComments('test comment')
            ->addAnswer('Привет мир!');

        $builder
            ->addField('testField')
            ->setAsField()
            ->setTitle('test')
        ;
        $builder
            ->addStatus('status')
            ->setArGroupCanDelete(array(2))
            ->setIsDefault(true);

        $builder->commit();

        $form = \CForm::GetList($by, $order, array(
            'ID' => $builder->getCurrentForm()->getId(),
        ), $isFiltered)->Fetch();

        $this->assertNotEmpty($form);
        $this->assertEquals($form['C_SORT'], 10);
        $this->assertNotEmpty($form['DESCRIPTION'], 'Description');
        $this->assertNotEmpty($form['NAME'], 'TestForm');
        $this->assertNotEmpty($form['USE_CAPTCHA'], 'Y');

        $res = \CFormField::GetList($builder->getCurrentForm()->getId(), 'ALL', $by, $order, array(), $isFiltered);

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

        $res = \CFormStatus::GetList($builder->getCurrentForm()->getId(), $by, $order, array(), $isFiltered)->Fetch();
        $this->assertEquals($res['TITLE'], 'status');
        $this->assertEquals($res['DEFAULT_VALUE'], 'Y');
    }


    public function testUpdate() {
        $builder = new FormBuilder();
        $builder
            ->getForm('TestForm')
            ->setName('MyTestForm');

        $field = $builder
            ->getField('testQuestion')
            ->setActive(true)
            ->setRequired(false);

        $field->removeAnswer('Привет мир!');

        $field
            ->addAnswer('Test')
            ->setValue('val1');

        $builder
            ->getStatus('status')
            ->setDescription('test22')
            ->setArGroupCanDelete(array(2, 3));

        $builder->commit();

        $form = \CForm::GetList($by, $order, array(
            'ID' => $builder->getCurrentForm()->getId(),
        ), $isFiltered)->Fetch();

        $this->assertNotEmpty($form);
        $this->assertNotEmpty($form['NAME'], 'MyTestForm');

        $res = \CFormField::GetList($builder->getCurrentForm()->getId(), 'ALL', $by, $order, array(), $isFiltered);

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

        $res = \CFormStatus::GetList($builder->getCurrentForm()->getId(), $by, $order, array(), $isFiltered)->Fetch();
        $this->assertEquals($res['DESCRIPTION'], 'test22');
    }

}