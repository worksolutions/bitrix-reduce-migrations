<?php
return array(
    'run' => array(
        'name' => 'WorkSolutions. Reduce Migrations',
        'report' => array(
            'completed' => 'Тестов',
            'assertions' => 'Проверок'
        )
    ),
    'cases' => array(
        \WS\ReduceMigrations\Tests\Cases\IblockBuilderCase::className() => array(
            'name' => 'IblockBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\HighLoadBlockBuilderCase::className() => array(
            'name' => 'HighLoadBlockBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\AgentBuilderCase::className() => array(
            'name' => 'AgentBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\EventsBuilderCase::className() => array(
            'name' => 'EventsBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\FormBuilderCase::className() => array(
            'name' => 'FormBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\TableBuilderCase::className() => array(
            'name' => 'TableBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
    )
);