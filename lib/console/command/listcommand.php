<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Scenario\ScriptScenario;

class ListCommand extends BaseCommand{

    private $registeredFixes;
    /** @var  \WS\ReduceMigrations\Localization */
    private $localization;

    protected function initParams($params) {
        $this->registeredFixes = array();
        $this->localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('admin')->fork('cli');
    }

    public function execute($callback = false) {
        $has = false;
        $notAppliedScenarios = $this->module->getNotAppliedScenarios();
        foreach ($notAppliedScenarios->groupByPriority() as $priority => $list) {
            /** @var ScriptScenario $notAppliedScenario */
            foreach ($list as $notAppliedScenario) {
                $this->registerFix($priority, $notAppliedScenario::name(), $notAppliedScenario::hash());
                $has = true;
            }
        }
        !$has && $this->console->printLine("Nothing to apply");
        $has && $this->printRegisteredFixes($notAppliedScenarios->getApproximateTime());
    }

    private function registerFix($priority, $name, $hash) {
        $this->registeredFixes[$priority][] = array(
            'name' => $name,
            'hash' => $hash,
        );
    }

    private function printRegisteredFixes($time) {
        //todo: make it prettier
        $this->console->printLine('List of migrations:');
        $maxLen = 0;
        foreach ($this->registeredFixes as $fixList) {
            foreach ($fixList as $fix) {
                if ($maxLen < strlen($fix['name'])) {
                    $maxLen = strlen($fix['name']);
                }
            }
        }
        $mask = "     %-#maxLen#.#maxLen#s %s";
        $mask = str_replace('#maxLen#', $maxLen + 1, $mask);
        foreach ($this->registeredFixes as $priority => $fixList) {
            $this->console->printLine(" $priority");
            foreach ($fixList as $fix) {
                $this->console->printLine(sprintf($mask, $fix['name'], substr($fix['hash'], 0, 8)));
            }
        }
        if ($time) {
            $this->console->printLine("Approximately applying time: $time min");
        }
    }


}
