<?php

namespace WS\ReduceMigrations\Tests\Cases;


use WS\ReduceMigrations\Builder\Entity\Property;
use WS\ReduceMigrations\Builder\IblockBuilder;
use WS\ReduceMigrations\Tests\AbstractCase;

class IblockBuilderCase extends AbstractCase {

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
        $iblock = \CIBlock::GetList(null, array(
            '=NAME' => 'testAddBlock'
        ))->Fetch();
        if ($iblock) {
            \CIBlock::Delete($iblock['ID']);
        }
        \CIBlockType::Delete('testAddType');
    }


    public function testAdd() {
        $iblockBuilder = new IblockBuilder();
        $iblockBuilder
            ->addIblockType('testAddType')
            ->setLang(array(
                'ru' => array(
                    'NAME' => 'Тестовый тип иб'
                ),
            ))
            ->setSort(10)
            ->setInRss(false)
        ;
        $iblockBuilder
            ->addIblock('testAddBlock')
            ->setIblockTypeId('testAddType')
            ->setSort(100)
            ->setName('testAddBlock')
            ->setCode('testAddBlock')
            ->setVersion(2)
            ->setSiteId('s1')
            ->setGroupId(array(
                '2' => 'R'
            ))
        ;

        $iblockBuilder
            ->addProperty('Цвет')
            ->setType(Property::TYPE_NUMBER)
            ->setIsRequired(true)
            ->setMultiple(true)
            ->setCode('color')
            ;
        $iblockBuilder
            ->addProperty('Картинка')
            ->setType(Property::TYPE_FILE)
            ;

        $iblockBuilder->addSection('Три', 'Тысячи', 'Чертей');
        $iblockBuilder->commit();

        $arType = \CIBlockType::GetList(null, array(
            'IBLOCK_TYPE_ID' => 'testAddType')
        )->Fetch();

        $this->assertNotEmpty($arType, "iblockType wasn't created");

        $arIblock = \CIBlock::GetList(null, array(
            'ID' => $iblockBuilder->getCurrentIblock()->getId()
        ))->Fetch();

        $this->assertNotEmpty($arIblock, "iblock wasn't created");
        $this->assertEquals($arIblock['CODE'], $iblockBuilder->getCurrentIblock()->code);
        $this->assertEquals($arIblock['NAME'], $iblockBuilder->getCurrentIblock()->name);
        $this->assertEquals($arIblock['SORT'], $iblockBuilder->getCurrentIblock()->sort);
        $this->assertEquals($arIblock['LID'], $iblockBuilder->getCurrentIblock()->siteId);

        $properties = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $iblockBuilder->getCurrentIblock()->getId()
        ));
        $props = array(
            'Картинка' => array(
                'PROPERTY_TYPE' => 'F'
            ),
            'Цвет' => array(
                'PROPERTY_TYPE' => 'N',
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
        $res = \CIBlockSection::GetList(array(), array(
            'IBLOCK_ID' => $iblockBuilder->getCurrentIblock()->getId()
        ));

        $this->assertEquals(3, $res->SelectedRowsCount());
        while($item = $res->Fetch()) {
            $this->assertTrue(in_array($item['NAME'], array('Три', 'Тысячи', 'Чертей')));
        }

    }

    public function testUpdateIblockType() {
        $iblockBuilder = new IblockBuilder();
        $type = $iblockBuilder
            ->getIblockType('testAddType')
            ->setSort(20)
        ;
        $iblockBuilder->commit();

        $arType = \CIBlockType::GetList(null, array(
                'IBLOCK_TYPE_ID' => 'testAddType')
        )->Fetch();

        $this->assertEquals($arType['SORT'], $type->sort);
    }

    public function testUpdate() {
        $iblockBuilder = new IblockBuilder();
        $iblockBuilder
            ->getIblock('testAddBlock')
            ->setSort(200)
            ->setCode('testAddBlock2')
            ->setVersion(2)
            ->setSiteId('s1')
            ->setGroupId(array(
                '2' => 'W'
            ))
        ;

        $iblockBuilder
            ->getProperty('Цвет')
            ->setType(Property::TYPE_STRING, Property::USER_TYPE_USER)
        ;
        $iblockBuilder
            ->getProperty('Картинка')
            ->setType(Property::TYPE_STRING)
            ->setCode('pic')
        ;
        $iblockBuilder->getSection('Три', 'Тысячи', 'Чертей')->setName('Четыре');

        $iblockBuilder->commit();

        $arIblock = \CIBlock::GetList(null, array(
            'ID' => $iblockBuilder->getCurrentIblock()->getId()
        ))->Fetch();


        $this->assertEquals($arIblock['CODE'], $iblockBuilder->getCurrentIblock()->code);
        $this->assertEquals($arIblock['NAME'], $iblockBuilder->getCurrentIblock()->name);
        $this->assertEquals($arIblock['SORT'], $iblockBuilder->getCurrentIblock()->sort);

        $properties = \CIBlockProperty::GetList(null, array(
            'IBLOCK_ID' => $iblockBuilder->getCurrentIblock()->getId()
        ));
        $props = array(
            'Картинка' => array(
                'PROPERTY_TYPE' => 'S',
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

        $res = \CIBlockSection::GetList(array(), array(
            'IBLOCK_ID' => $iblockBuilder->getCurrentIblock()->getId()
        ));

        $this->assertEquals(3, $res->SelectedRowsCount());
        while($item = $res->Fetch()) {
            $this->assertTrue(in_array($item['NAME'], array('Четыре', 'Тысячи', 'Чертей')));
        }
    }

}