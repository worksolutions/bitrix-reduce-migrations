<?php

namespace WS\ReduceMigrations\Tests\Cases;

use WS\ReduceMigrations\Builder\Entity\Iblock;
use WS\ReduceMigrations\Builder\Entity\IblockType;
use WS\ReduceMigrations\Builder\IblockBuilder;
use WS\ReduceMigrations\Tests\AbstractCase;

class IblockBuilderCase extends AbstractCase {

    public function name() {
        return $this->localization->message('name');
    }

    public function description() {
        return $this->localization->message('description');
    }

    public function close() {
        $iblock = \CIBlock::GetList(null, array(
            '=NAME' => 'testAddBlock'
        ))->Fetch();
        if ($iblock) {
            \CIBlock::Delete($iblock['ID']);
        }
        \CIBlockType::Delete('testAddType');
    }

    public function testAlgorithm() {
        $iblockId = $this->add();
        $this->updateIblockType();
        $this->update($iblockId);

    }

    private function add() {
        $builder = new IblockBuilder();
        $builder->createIblockType('testAddType', function (IblockType $type) {
            $type
                ->inRss(false)
                ->sort(10)
                ->lang(array(
                    'ru' => array(
                        'NAME' => 'Тестовый тип иб'
                    ),
                ));
        });

        $iblock = $builder->createIblock('testAddType', 'testAddBlock', function (Iblock $iblock) {
            $iblock
                ->code('testAddBlock')
                ->version(2)
                ->siteId('s1')
                ->groupId(array('2' => 'R'))
                ->sort(100)
            ;
            $iblock
                ->addProperty('Цвет')
                ->typeString()
                ->required()
                ->multiple()
                ->code('color')
            ;

            $iblock
                ->addProperty('Картинка')
                ->typeFile()
                ->code('picture');
        });


        $arType = \CIBlockType::GetList(null, array(
                'IBLOCK_TYPE_ID' => 'testAddType')
        )->Fetch();

        $this->assertNotEmpty($arType, "iblockType wasn't created");

        $arIblock = \CIBlock::GetList(null, array(
            'ID' => $iblock->getId()
        ))->Fetch();

        $this->assertNotEmpty($arIblock, "iblock wasn't created");
        $this->assertEquals($arIblock['CODE'], $iblock->getAttribute('CODE'));
        $this->assertEquals($arIblock['NAME'], $iblock->getAttribute('NAME'));
        $this->assertEquals($arIblock['SORT'], $iblock->getAttribute('SORT'));
        $this->assertEquals($arIblock['LID'], $iblock->getAttribute('SITE_ID'));

        $properties = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $iblock->getId()
        ));
        $props = array(
            'Картинка' => array(
                'PROPERTY_TYPE' => 'F'
            ),
            'Цвет' => array(
                'PROPERTY_TYPE' => 'S',
                'IS_REQUIRED' => 'Y',
                'MULTIPLE' => 'Y',
            ),
        );
        while ($property = $properties->Fetch()) {
            $this->assertNotEmpty($props[$property['NAME']]);
            if ($property['NAME'] == 'Картинка') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
            }

            if ($property['NAME'] == 'Цвет') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
                $this->assertEquals($props[$property['NAME']]['IS_REQUIRED'], $property['IS_REQUIRED']);
                $this->assertEquals($props[$property['NAME']]['MULTIPLE'], $property['MULTIPLE']);
            }
        }

        return $iblock->getId();
    }

    public function updateIblockType() {
        $iblockBuilder = new IblockBuilder();
        $type = $iblockBuilder->updateIblockType('testAddType', function(IblockType $type) {
            $type->sort(20);
        });

        $arType = \CIBlockType::GetList(null, array(
            '=ID' => 'testAddType'
        ))->Fetch();

        $this->assertEquals($arType['SORT'], $type->getAttribute('SORT'));
    }

    public function update($iblockId) {
        $iblockBuilder = new IblockBuilder();
        $iblock = $iblockBuilder->updateIblock($iblockId, function (Iblock $iblock) {
            $iblock->sort(200);
            $iblock
                ->code('testAddBlock2')
                ->version(2);
            $iblock
                ->updateProperty('Цвет')
                ->typeUser();
            $iblock
                ->updateProperty('Картинка')
                ->code('pic')
                ->typeNumber();
        });

        $arIblock = \CIBlock::GetList(null, array(
            'ID' => $iblockId
        ))->Fetch();


        $this->assertEquals($arIblock['CODE'], $iblock->getAttribute('CODE'));
        $this->assertEquals($arIblock['VERSION'], $iblock->getAttribute('VERSION'));

        $properties = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $iblockId
        ));
        $props = array(
            'Картинка' => array(
                'PROPERTY_TYPE' => 'N',
                'CODE' => 'pic',
            ),
            'Цвет' => array(
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE' => 'UserID',
            ),
        );
        while ($property = $properties->Fetch()) {
            $this->assertNotEmpty($props[$property['NAME']]);
            if ($property['NAME'] == 'Картинка') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
                $this->assertEquals($props[$property['NAME']]['CODE'], $property['CODE']);
            }

            if ($property['NAME'] == 'Цвет') {
                $this->assertEquals($props[$property['NAME']]['PROPERTY_TYPE'], $property['PROPERTY_TYPE']);
                $this->assertEquals($props[$property['NAME']]['USER_TYPE'], $property['USER_TYPE']);
            }
        }
    }

}