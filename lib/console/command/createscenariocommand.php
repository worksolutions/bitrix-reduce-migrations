<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class CreateScenarioCommand extends BaseCommand {

    private $name;
    private $priority;
    private $time;

    const PARAM_NAME = '-n';
    const PARAM_PRIORITY = '-p';
    const PARAM_TIME = '-t';

    const HIGH_PRIORITY_SHORTCUT = 'h';
    const MEDIUM_PRIORITY_SHORTCUT = 'm';
    const OPTIONAL_PRIORITY_SHORTCUT = 'o';

    private function availablePriorities() {
       return array(
           self::HIGH_PRIORITY_SHORTCUT => ScriptScenario::PRIORITY_HIGH,
           self::MEDIUM_PRIORITY_SHORTCUT => ScriptScenario::PRIORITY_MEDIUM,
           self::OPTIONAL_PRIORITY_SHORTCUT => ScriptScenario::PRIORITY_OPTIONAL,
       );
    }

    protected function initParams($params) {
        $this->name = isset($params[self::PARAM_NAME]) ? trim($params[self::PARAM_NAME]) : false;
        $this->priority = isset($params[self::PARAM_PRIORITY]) ? trim($params[self::PARAM_PRIORITY]) : false;
        $this->time = isset($params[self::PARAM_TIME]) ? trim($params[self::PARAM_TIME]) : false;
    }

    private function getName() {
        if ($this->name) {
            return $this->name;
        }
        $this->console
            ->printLine('Enter name:');
        $name = $this->console
            ->readLine();
        while (!strlen(trim($name))) {
            $this->console
                ->printLine("Name mustn't be empty. Enter name:");
            $name = $this->console
                ->readLine();
        }
        return $name;
    }

    private function getPriority() {
        $priority = $this->normalizePriority($this->priority);
        while (!$priority) {
            $this->console
                ->printLine('Enter priority(h - high, m - medium, o - optional):');
            $priority = $this->normalizePriority($this->console
                ->readLine());
        }
        return $priority;
    }

    private function normalizePriority($priority) {
        $priorities = $this->availablePriorities();
        if ($priorities[$priority]) {
            return $priorities[$priority];
        }
        if (in_array($priority, $priorities, true)) {
            return $priority;
        }

        return false;
    }

    public function execute($callback = false) {
        try {
            $fileName = $this->module->createScenario($this->prepareName($this->getName()), $this->getPriority(), (int)$this->time);
        } catch (\Exception $e) {
            $this->console->printLine('An error occurred saving file', Console::OUTPUT_ERROR);
            $this->console->printLine($e->getMessage());
            return;
        }
        $this->console->printLine($fileName, Console::OUTPUT_SUCCESS);
    }

    public function prepareName($name) {
        /* @var \CMain */
        global $APPLICATION;
        $name = $APPLICATION->ConvertCharset($name, mb_detect_encoding($name), LANG_CHARSET);
        return $name;
    }
}
