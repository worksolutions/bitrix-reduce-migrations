<?php

namespace WS\ReduceMigrations\Tests\Cases;

use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Builder\Entity\Agent;
use WS\ReduceMigrations\Tests\AbstractCase;

class AgentBuilderCase extends AbstractCase {

    public function name() {
        return $this->localization->message('name');
    }

    public function description() {
        return $this->localization->message('description');
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
        $builder->addAgent('abs(0);', function (Agent $agent) use ($date) {
            $agent
                ->sort(23)
                ->active(true)
                ->nextExec($date);
        });
        $agent = \CAgent::GetList(null, array(
            'NAME' => 'abs(0);'
        ))->Fetch();

        $this->assertNotEmpty($agent);
        $this->assertEquals($agent['NAME'], 'abs(0);');
        $this->assertEquals($agent['SORT'], 23);
        $this->assertEquals($agent['ACTIVE'], "Y");
        $this->assertEquals($agent['NEXT_EXEC'], $date->format('d.m.Y H:i:s'));
    }


    public function testUpdate() {
        $builder = new \WS\ReduceMigrations\Builder\AgentBuilder();
        $builder
            ->updateAgent('abs(0);', function (Agent $agent) {
                $agent
                    ->active(false)
                    ->isPeriod(true);
            });

        $agent = \CAgent::GetList(null, array(
            'NAME' => $builder->getCurrentAgent()->callback
        ))->Fetch();

        $this->assertNotEmpty($agent);
        $this->assertEquals($agent['ACTIVE'], 'N');
        $this->assertEquals($agent['IS_PERIOD'], 'Y');
    }

}