<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class CreateScenarioCommand extends BaseCommand {

    private $name;
    private $priority;
    private $time;

    private function availablePriorities() {
       return [
           'h' => ScriptScenario::PRIORITY_HIGH,
           'm' => ScriptScenario::PRIORITY_MEDIUM,
           'o' => ScriptScenario::PRIORITY_OPTIONAL,
       ];
    }

    protected function initParams($params) {
        $this->name = isset($params['-n']) ? trim($params['-n']) : false;
        $this->priority = isset($params['-p']) ? trim($params['-p']) : false;
        $this->time = isset($params['-t']) ? trim($params['-t']) : false;
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
                ->printLine("Enter priority(h - high, m - medium, o - optional):");
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
        if (in_array($priority, $priorities)) {
            return $priority;
        }

        return false;
    }

    public function execute($callback = false) {
        try {
            $fileName = $this->module->createScenario($this->getName(), $this->getPriority(), (int)$this->time);
        } catch (\Exception $e) {
            $this->console->printLine("An error occurred saving file", Console::OUTPUT_ERROR);
            return;
        }
        $this->console->printLine($fileName, Console::OUTPUT_SUCCESS);
    }

}
