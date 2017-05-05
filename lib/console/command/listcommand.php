<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\Pear\Console_Table;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class ListCommand extends BaseCommand{

    private $registeredFixes;
    /** @var  \WS\ReduceMigrations\Localization */
    private $localization;

    protected function initParams($params) {
        $this->registeredFixes = array();
        $this->localization = Module::getInstance()->getLocalization('admin')->fork('cli');
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
        !$has && $this->console->printLine('Nothing to apply', Console::OUTPUT_SUCCESS);
        $has && $this->printRegisteredFixes($notAppliedScenarios->getApproximateTime());
    }

    /**
     * @param $priority
     * @param ScriptScenario $notAppliedScenario
     */
    private function registerFix($priority, $notAppliedScenario) {
        $this->registeredFixes[$priority][] = array(
            'name' => $notAppliedScenario::name(),
            'hash' => $notAppliedScenario::getShortenedHash(),
            'time' => $this->console->formatTime($notAppliedScenario::approximatelyTime()),
        );
    }

    private function printRegisteredFixes($time) {
        $table = new Console_Table();

        $table->setHeaders(array(
            'Priority', 'Name', 'Hash', '~time'
        ));

        foreach ($this->registeredFixes as $priority => $fixList) {

            $table->addRow(array(
                $priority, '', '', ''
            ));

            foreach ($fixList as $fix) {
                $table->addRow(array(
                    '', $fix['name'], $fix['hash'], $fix['time']
                ));
            }
        }
        $this->console
            ->printLine('List of migrations:')
            ->printLine($table->getTable());
        if ($time) {
            $this->console
                ->printLine(sprintf('Approximately applying time: %s', $this->console->formatTime($time)))
                ->printLine('');
        }
    }


}
