<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\Formatter\Table;
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
                $this->registerFix($priority, $notAppliedScenario);
                $has = true;
            }
        }
        !$has && $this->console->printLine("Nothing to apply", Console::OUTPUT_SUCCESS);
        $has && $this->printRegisteredFixes($notAppliedScenarios->getApproximateTime());
    }

    /**
     * @param $priority
     * @param ScriptScenario $notAppliedScenario
     */
    private function registerFix($priority, $notAppliedScenario) {
        $this->registeredFixes[$priority][] = array(
            'name' => $notAppliedScenario::name(),
            'hash' => substr($notAppliedScenario::hash(), 0, 8),
            'time' => $notAppliedScenario::approximatelyTime() . " min",
        );
    }

    private function printRegisteredFixes($time) {
        $table = new Table('List of migrations:', $this->console);
        $table->addRow("\tMigrationName", "Hash", "Approximate time");
        foreach ($this->registeredFixes as $priority => $fixList) {
            $table->addRow(" {$priority}:");
            foreach ($fixList as $fix) {
                $table->addRow("\t" . $fix['name'], $fix['hash'], $fix['time']);
            }
        }
        $this->console->printLine($table);
        if ($time) {
            $this->console->printLine("Approximately applying time: $time min");
        }
    }


}
