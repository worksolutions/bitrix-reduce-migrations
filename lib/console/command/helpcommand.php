<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\Formatter\Table;

class HelpCommand extends BaseCommand {

    private $command;

    public function initParams($params) {
        $this->command = $params[0] ? : false;
    }

    /**
     * @return array
     */
    private function commandsInfo() {
        $commands = [
            'list' => [
                'info' => 'List of new migrations',
                'params' => [],
                'examples' => [
                    $this->getCommandTemplate('list')
                ],
            ],
            'apply' => [
                'info' => 'Apply new migrations',
                'params' => [
                    'hash' => 'apply migration with `hash`',
                    '-f' => 'apply migration without approve',
                    '--skip-optional' => 'skip migration with priority `optional`',
                ],
                'examples' => [
                    $this->getCommandTemplate('apply', '49ea590e'),
                    $this->getCommandTemplate('apply', '-f'),
                    $this->getCommandTemplate('apply', '-f --skip-optional'),
                ],
            ],
            'rollback' => [
                'info' => 'Rollback last applied migrations',
                'params' => [
                    'hash' => 'rollback migration with `hash`',
                    '--count=<count>' => 'rollback last `count` of migrations',
                    '--to-hash=<hash>' => 'rollback migrations from last to migration with hash=`hash`',
                ],
                'examples' => [
                    $this->getCommandTemplate('rollback'),
                    $this->getCommandTemplate('rollback', '49ea590e'),
                    $this->getCommandTemplate('rollback', '--count=5'),
                    $this->getCommandTemplate('rollback', '--to-hash=49ea590e'),
                ],
            ],
            'createScenario' => [
                'info' => 'Apply new migrations',
                'params' => [
                    '-n=<name>' => 'migration name',
                    '-p=<priority>' => 'migration priority: h - high, m - middle, o - optional',
                    '-t=<time>' => 'migration approximately time in minutes',
                ],
                'examples' => [
                    $this->getCommandTemplate('createScenario', '-n="Hello world" -p=h -t=5'),
                ],
            ],
            'history' => [
                'info' => 'Create new migration scenario',
                'params' => [
                    'count' => 'show last `count` applied migrations'
                ],
                'examples' => [
                    $this->getCommandTemplate('history'),
                    $this->getCommandTemplate('history', 4),
                ],
            ],
        ];

        return $commands;
    }

    public function execute($callback = false) {
        if ($this->command) {
            $this->showInfoByCommand($this->command);
        } else {
            $this->showConsoleInfo();
        }
    }

    private function showInfoByCommand($command) {
        $commands = $this->commandsInfo();

        $commandInfo = $commands[$command];

        $params = implode(' ', array_map(function ($item) {
            return "[" . $item . "]";
        }, array_keys($commandInfo['params'])));

        $table = new Table($this->console->colorize('Params:', Console::OUTPUT_PROGRESS));
        foreach ($commandInfo['params'] as $param => $info) {
            $table->addRow($this->console->colorize("   " . $param, Console::OUTPUT_SUCCESS), $info);
        }
        $exampleTable = new Table($this->console->colorize('Examples:', Console::OUTPUT_PROGRESS));
        foreach ($commandInfo['examples'] as $example) {
            $exampleTable->addRow($example);
        }
        $this->console
            ->printLine('Usage:', Console::OUTPUT_PROGRESS)
            ->printLine($this->getCommandTemplate($command, $params))
            ->printLine('')
            ->printLine($table)
            ->printLine($exampleTable)
        ;
    }

    private function showConsoleInfo() {
        $commandsInfo = $this->commandsInfo();
        $table = new Table($this->console->colorize('Commands:', Console::OUTPUT_PROGRESS));

        foreach ($commandsInfo as $param => $info) {
            $table->addRow($this->console->colorize("   " . $param, Console::OUTPUT_SUCCESS), $info['info']);
        }
        $this->console
            ->printLine("Usage:", Console::OUTPUT_PROGRESS)
            ->printLine($this->getCommandTemplate("<command>", "<command_params>"))
            ->printLine("")
            ->printLine($table)
            ->printLine("Command params:", Console::OUTPUT_PROGRESS)
            ->printLine(sprintf('   %s           %s',
                $this->console->colorize('--help', Console::OUTPUT_SUCCESS),
                'Show full info for command'
            ))
        ;


    }

    /**
     * @param $command
     * @param $params
     *
     * @return string
     */
    private function getCommandTemplate($command, $params = '') {
        return "   php ws_reducemigrations.php {$command} {$params}";
    }
}
