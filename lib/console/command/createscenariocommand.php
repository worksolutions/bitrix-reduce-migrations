<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;

class CreateScenarioCommand extends BaseCommand {

    private $name;
    private $description;

    private function isParam($value) {
        $params = array(
            '-n',
            '-d'
        );
        return in_array(trim($value), $params);
    }

    protected function initParams($params) {
        $this->name = false;
        $this->description = false;
        if ($index = array_search('-n', $params)) {
            isset($params[$index + 1]) && !$this->isParam($params[$index + 1]) && $this->name = trim($params[$index + 1]);
        }
        if ($index = array_search('-d', $params)) {
            isset($params[$index + 1]) && !$this->isParam($params[$index + 1]) && $this->description = trim($params[$index + 1]);
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

    private function getDescription() {
        if ($this->description) {
            return $this->description;
        }
        $this->console
            ->printLine('Enter description:');
        return $this->console
            ->readLine();
    }
    public function execute($callback = false) {
        $this->name = $this->getName();
        $this->description = $this->getDescription();

        $templateContent = file_get_contents(__DIR__.'/../../../data/scenarioTemplate.tpl');

        $arReplace = array(
            '#class_name#' => $className = 'ws_m_' . time(). '_' . \CUtil::translit($this->name, LANGUAGE_ID),
            '#name#' => $this->name,
            '#description#' => $this->description,
            '#db_version#' => $this->module->getPlatformVersion()->getValue(),
            '#owner#' => $this->module->getPlatformVersion()->getOwner()
        );
        $classContent = str_replace(array_keys($arReplace), array_values($arReplace), $templateContent);
        $fileName = $className . '.php';
        try {
            $fileName = $this->module->putScriptClass($fileName, $classContent);
        } catch (\Exception $e) {
            $this->console->printLine("An error occurred saving file", Console::OUTPUT_ERROR);
            return;
        }
        $this->console->printLine($fileName, Console::OUTPUT_SUCCESS);
    }

}
