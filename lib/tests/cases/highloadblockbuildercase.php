<?php

namespace WS\ReduceMigrations\Tests\Cases;

use Bitrix\Highloadblock\HighloadBlockTable;
use WS\ReduceMigrations\Builder\Entity\UserField;
use WS\ReduceMigrations\Builder\HighLoadBlockBuilder;
use WS\ReduceMigrations\Tests\AbstractCase;

class HighLoadBlockBuilderCase extends AbstractCase {

    public function name() {
        return $this->localization->message('name');
    }

    public function description() {
        return $this->localization->message('description');
    }

    public function init() {

        \CModule::IncludeModule('iblock');
    }

    public function close() {
        $arIblock = HighloadBlockTable::getList(array(
            'filter' => array(
                'TABLE_NAME' => 'test_highloadblock'
            )
        ))->fetch();

        HighloadBlockTable::delete($arIblock['ID']);
    }


    public function testAdd() {
        $builder = new HighLoadBlockBuilder();
        $builder
            ->addHLBlock('TestBlock', 'test_highloadblock')
        ;
        $prop = $builder
            ->addField('uf_test1')
            ->setSort(10)
            ->setLabel(['ru' => 'Тест'])
            ->setUserTypeId(UserField::TYPE_ENUMERATION)
        ;
        $prop->addEnum('Тест1');
        $prop->addEnum('Тест2');
        $prop->addEnum('Тест3');

        $builder
            ->addField('uf_test2')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_HLBLOCK);

        $builder
            ->addField('uf_test3')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_BOOLEAN);

        $builder
            ->addField('uf_test4')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_DATETIME);

        $builder
            ->addField('uf_test5')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_IBLOCK_ELEMENT);

        $builder
            ->addField('uf_test6')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_VOTE);

        $builder
            ->addField('uf_test7')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_VIDEO);

        $builder
            ->addField('uf_test8')
            ->setLabel(['ru' => 'Тест2'])
            ->setUserTypeId(UserField::TYPE_IBLOCK_SECTION);
        
        $builder->commit();

        $arIblock = HighloadBlockTable::getList(array(
            'filter' => array(
                'ID' => $builder->getCurrentHighLoadBlock()->getId()
            )
        ))->fetch();

        $this->assertNotEmpty($arIblock, "hlblock wasn't created");
        $this->assertEquals($arIblock['TABLE_NAME'], $builder->getCurrentHighLoadBlock()->tableName);
        $this->assertEquals($arIblock['NAME'], $builder->getCurrentHighLoadBlock()->name);

        $fields = \CUserTypeEntity::GetList(null, array(
            'ENTITY_ID' => "HLBLOCK_" . $builder->getCurrentHighLoadBlock()->getId(),
        ));

        $this->assertEquals($fields->SelectedRowsCount(), 8);
        while ($field = $fields->Fetch()) {
            $field['NAME'] == 'uf_test5' && $this->assertEquals($field['USER_TYPE_ID'], UserField::TYPE_IBLOCK_ELEMENT);
        }

    }


    public function testUpdate() {
        $builder = new HighLoadBlockBuilder();
        $builder
            ->getHLBlock('test_highloadblock')
            ->setName('TestBlock2')
        ;

        $prop = $builder
            ->getField('uf_test1')
            ->setMultiple(true)
            ->setRequired(true)
        ;
        $prop->updateEnum('Тест1')->setXmlId('test1');
        $prop->removeEnum('Тест2');

        $builder->commit();

        $arIblock = HighloadBlockTable::getList(array(
            'filter' => array(
                'ID' => $builder->getCurrentHighLoadBlock()->getId()
            )
        ))->fetch();

        $this->assertEquals($arIblock['TABLE_NAME'], $builder->getCurrentHighLoadBlock()->tableName);
        $this->assertEquals($arIblock['NAME'], $builder->getCurrentHighLoadBlock()->name);

        $res = \CUserFieldEnum::GetList(null, array(
            'USER_FIELD_ID' => $builder->getCurrentHighLoadBlock()->getId(),
            'VALUE' => 'Тест2',
        ))->Fetch();

        $this->assertEmpty($res);

        $res = \CUserFieldEnum::GetList(null, array(
            'USER_FIELD_ID' => $prop->getId(),
            'VALUE' => 'Тест1',
        ))->Fetch();

        $this->assertNotEmpty($res);
        $this->assertEquals($res['XML_ID'], 'test1');
    }

}