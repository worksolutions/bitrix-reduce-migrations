<?php

namespace WS\ReduceMigrations\Tests\Cases;

use Bitrix\Highloadblock\HighloadBlockTable;
use WS\ReduceMigrations\Builder\Entity\HighLoadBlock;
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
        $block = $builder->addHLBlock('TestBlock', 'test_highloadblock', function (HighLoadBlock $block) {
            $prop = $block
                ->addField('uf_test1')
                ->sort(10)
                ->label(array('ru' => 'Тест'))
                ->type(UserField::TYPE_ENUMERATION);

            $prop->addEnum('Тест1');
            $prop->addEnum('Тест2');
            $prop->addEnum('Тест3');

            $block
                ->addField('uf_test2')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_HLBLOCK);

            $block
                ->addField('uf_test3')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_BOOLEAN);

            $block
                ->addField('uf_test4')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_DATETIME);

            $block
                ->addField('uf_test5')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_IBLOCK_ELEMENT);

            $block
                ->addField('uf_test6')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_VOTE);

            $block
                ->addField('uf_test7')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_VIDEO);

            $block
                ->addField('uf_test8')
                ->label(array('ru' => 'Тест2'))
                ->type(UserField::TYPE_IBLOCK_SECTION);
        });


        $arIblock = HighloadBlockTable::getList(array(
            'filter' => array(
                'ID' => $block->getId()
            )
        ))->fetch();

        $this->assertNotEmpty($arIblock, "hlblock wasn't created");
        $this->assertEquals($arIblock['TABLE_NAME'], $block->getAttribute('TABLE_NAME'));
        $this->assertEquals($arIblock['NAME'], $block->getAttribute('NAME'));

        $fields = \CUserTypeEntity::GetList(null, array(
            'ENTITY_ID' => "HLBLOCK_" . $block->getId(),
        ));

        $this->assertEquals($fields->SelectedRowsCount(), 8);
        while ($field = $fields->Fetch()) {
            $field['NAME'] == 'uf_test5' && $this->assertEquals($field['USER_TYPE_ID'], UserField::TYPE_IBLOCK_ELEMENT);
        }

    }

    public function testUpdate() {
        $builder = new HighLoadBlockBuilder();
        $block = $builder->updateHLBlock('test_highloadblock', function (HighLoadBlock $block) {
            $block->name('TestBlock2');
            $prop = $block
                ->updateField('uf_test1')
                ->multiple(true)
                ->required(true);
            $prop->updateEnum('Тест1')->xmlId('test1');
            $prop->removeEnum('Тест2');
        });
        $prop = $block->updateField('uf_test1');

        $arIblock = HighloadBlockTable::getList(array(
            'filter' => array(
                'ID' => $block->getId()
            )
        ))->fetch();

        $this->assertEquals($arIblock['NAME'], $block->getAttribute('NAME'));

        $res = \CUserFieldEnum::GetList(null, array(
            'USER_FIELD_ID' => $block->getId(),
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