<?php
return array(
    'run' => array(
        'name' => 'WorkSolutions. Reduce Migrations module',
        'report' => array(
            'completed' => 'Completed',
            'assertions' => 'Assertions'
        )
    ),
    'cases' => array(
        \WS\ReduceMigrations\Tests\Cases\IblockBuilderCase::className() => array(
            'name' => 'IblockBuilder test',
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