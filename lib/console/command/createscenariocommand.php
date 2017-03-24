<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;

class CreateScenarioCommand extends BaseCommand {

    private $name;
    private $priority;

    private function isParam($value) {
        $params = array(
            '-n',
            '-p'
        );
        return in_array(trim($value), $params);
    }

    protected function initParams($params) {
        $this->name = false;
        $this->priority = false;
        if ($index = array_search('-n', $params)) {
            isset($params[$index + 1]) && !$this->isParam($params[$index + 1]) && $this->name = trim($params[$index + 1]);
        }
        if ($index = array_search('-p', $params)) {
            isset($params[$index + 1]) && !$this->isParam($params[$index + 1]) && $this->priority = trim($params[$index + 1]);
        }
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
        if ($this->priority) {
            return $this->priority;
        }
        $this->console
            ->printLine('Enter priority:');
        return $this->console
            ->readLine();
    }

    public function execute($callback = false) {
        try {
            $fileName = $this->module->createScrenario($this->getName(), $this->getPriority());
        } catch (\Exception $e) {
            $this->console->printLine("An error occurred saving file", Console::OUTPUT_ERROR);
            return;
        }
        $this->console->printLine($fileName, Console::OUTPUT_SUCCESS);
    }

}
