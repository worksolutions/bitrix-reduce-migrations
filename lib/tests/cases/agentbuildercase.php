<?php

namespace WS\ReduceMigrations\Tests\Cases;

use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Tests\AbstractCase;

class AgentBuilderCase extends AbstractCase {

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
        $agent = \CAgent::GetList(null, array(
            'NAME' => 'abs(0);'
        ))->Fetch();
        \CAgent::Delete($agent['ID']);
    }


    public function testAdd() {
        $date = new DateTime();
        $date->add('+1 day');
        $builder = new \WS\ReduceMigrations\Builder\AgentBuilder();
        $builder
            ->addAgent('abs(0);')
            ->setSort(23)
            ->setActive(true)
            ->setNextExec($date);
        $builder->commit();

        $agent = \CAgent::GetList(null, array(
            'NAME' => $builder->getCurrentAgent()->callback
        ))->Fetch();

        $this->assertNotEmpty($agent);
        $this->assertEquals($agent['NAME'], $builder->getCurrentAgent()->callback);
        $this->assertEquals($agent['SORT'], 23);
        $this->assertEquals($agent['ACTIVE'], "Y");
        $this->assertEquals($agent['NEXT_EXEC'], $date->format('d.m.Y H:i:s'));
    }


    public function testUpdate() {
        $builder = new \WS\ReduceMigrations\Builder\AgentBuilder();
        $builder
            ->getAgent('abs(0);')
            ->setActive(false)
            ->setIsPeriod(true);

        $builder->commit();

        $agent = \CAgent::GetList(null, array(
            'NAME' => $builder->getCurrentAgent()->callback
        ))->Fetch();

        $this->assertNotEmpty($agent);
        $this->assertEquals($agent['NAME'], $builder->getCurrentAgent()->callback);
        $this->assertEquals($agent['ACTIVE'], 'N');
        $this->assertEquals($agent['IS_PERIOD'], 'Y');
    }

}