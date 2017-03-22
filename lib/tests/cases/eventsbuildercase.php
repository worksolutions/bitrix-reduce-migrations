<?php

namespace WS\ReduceMigrations\Tests\Cases;

use Bitrix\Main\Mail\Internal\EventMessageTable;
use WS\ReduceMigrations\Builder\Entity\EventMessage;
use WS\ReduceMigrations\Builder\EventsBuilder;
use WS\ReduceMigrations\Tests\AbstractCase;

class EventsBuilderCase extends AbstractCase {

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
        $eventType = \CEventType::GetList(array(
            'TYPE_ID' => 'WS_MIGRATION_TEST_EVENT',
            'LID' => 'en'
        ))->Fetch();
        $gw = new \CEventType;
        $gw->Delete($eventType['ID']);
    }

    public function testAdd() {
        $builder = new EventsBuilder();
        $builder
            ->addEventType('WS_MIGRATION_TEST_EVENT', 'ru')
            ->setName('Тестовое событие миграций')
            ->setSort(10)
            ->setDescription('#TEST# - test');

        $builder
            ->addEventMessage('#EMAIL_FROM#', '#EMAIL_TO#', 's1')
            ->setSubject('Hello')
            ->setBodyType(EventMessage::BODY_TYPE_HTML)
            ->setActive(true)
            ->setMessage('Hello #TEST#!')
        ;

        $builder
            ->addEventMessage('#FROM#', '#TO#', 's1')
            ->setSubject('Hi')
            ->setActive(false)
            ->setBodyType(EventMessage::BODY_TYPE_TEXT)
            ->setMessage('Hi #TEST#!')
        ;

        $builder->commit();

        $eventType = \CEventType::GetList(array(
            'TYPE_ID' => 'WS_MIGRATION_TEST_EVENT',
            'LID' => 'ru'
        ))->Fetch();
        $this->assertNotEmpty($eventType);
        $this->assertEquals($eventType['SORT'], 10);
        $this->assertNotEmpty($eventType['DESCRIPTION'], '#TEST# - test');
        $this->assertNotEmpty($eventType['NAME'], 'Тестовое событие миграций');

        $res = EventMessageTable::getList(array(
            'filter' => array(
                'EVENT_NAME' => 'WS_MIGRATION_TEST_EVENT'
            )
        ));
        $this->assertEquals($res->getSelectedRowsCount(), 2);
        while ($item = $res->fetch()) {
            if ($item['SUBJECT'] == 'Hi') {
                $this->assertEquals($item['BODY_TYPE'], 'text');
                $this->assertEquals($item['MESSAGE'], 'Hi #TEST#!');
                $this->assertEquals($item['LID'], 's1');
                $this->assertEquals($item['ACTIVE'], 'N');
                $this->assertEquals($item['EMAIL_FROM'], '#FROM#');
                $this->assertEquals($item['EMAIL_TO'], '#TO#');
            }
        }
    }


    public function testUpdate() {
        $builder = new EventsBuilder();
        $builder
            ->getEventType('WS_MIGRATION_TEST_EVENT', 'ru')
            ->setLid('en')
            ->setName('Тестовое событие');

        foreach ($builder->getEventMessages() as $message) {
            if ($message->subject == 'Hello') {
                $message->remove();
            }
            $message->setBcc('#BCC#');
        }

        $builder->commit();

        $eventType = \CEventType::GetList(array(
            'TYPE_ID' => 'WS_MIGRATION_TEST_EVENT',
            'LID' => 'en'
        ))->Fetch();
        $this->assertTrue(!empty($eventType));
        $this->assertNotEmpty($eventType['NAME'], 'Тестовое событие');

        $res = EventMessageTable::getList(array(
            'filter' => array(
                'EVENT_NAME' => 'WS_MIGRATION_TEST_EVENT'
            )
        ));
        $this->assertEquals($res->getSelectedRowsCount(), 1);
        while ($item = $res->fetch()) {
            $this->assertEquals($item['BCC'], '#BCC#');
        }
    }

}