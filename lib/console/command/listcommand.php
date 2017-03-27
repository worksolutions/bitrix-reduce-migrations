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
        $time = 0;
        foreach ($this->module->getNotAppliedScenarios() as $priority => $list) {
            /** @var ScriptScenario $notAppliedScenario */
            foreach ($list as $notAppliedScenario) {
                $time += (double)$notAppliedScenario::approximatelyTime();
                $this->registerFix($notAppliedScenario::name());
                $has = true;
            }
        }
        !$has && $this->console->printLine("Nothing to apply");
        $has && $this->printRegisteredFixes($time);
    }

    private function registerFix($name) {
        $this->registeredFixes[] = $name;
    }

    private function printRegisteredFixes($time) {
        $this->console->printLine('List of migrations:');
        foreach ($this->registeredFixes as $name) {
            $this->console->printLine("   " . $name);
        }
        if ($time) {
            $this->console->printLine("Approximately applying time: $time min");
        }
    }


}
