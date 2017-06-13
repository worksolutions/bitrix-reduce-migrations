<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Console\Pear\ConsoleTable;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\Scenario\ScriptScenario;

class ListCommand extends BaseCommand{

    private $registeredFixes;
    /** @var  \WS\ReduceMigrations\Localization */
    private $localization;

    /**
     * @var string
     */
    private $hash = "";

    protected function initParams($params) {
        $this->registeredFixes = array();
        $this->localization = Module::getInstance()->getLocalization('admin')->fork('cli');
        $this->hash = $params[0];
    }

    public function execute($callback = false) {
        $has = false;
        $notAppliedScenarios = $this->module->getNotAppliedScenarios();
        foreach ($notAppliedScenarios->groupByPriority() as $priority => $list) {
            /** @var ScriptScenario $notAppliedScenario */
            foreach ($list as $notAppliedScenario) {
                if ($this->hash && strpos($notAppliedScenario::hash(), $this->hash) === false) {
                    continue;
                }

                $this->registerFix($priority, $notAppliedScenario);
                $has = true;
            }
        }
        !$has && $this->console->printLine("Nothing to apply\n", Console::OUTPUT_SUCCESS);
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
        $table = new ConsoleTable();
        $table->setCharset(LANG_CHARSET);

        $table->setHeaders(array(
            'Priority', 'Name', 'Hash', '~Duration'
        ));

        $table->setCellsLength(array(10, 80, 10, 10));

        $count = 0;
        foreach ($this->registeredFixes as $priority => $fixList) {
            $priorityPos = (int) ((count($fixList) - 1) / 2);

            $fixList = array_values($fixList);
            foreach ($fixList as $k => $fix) {
                $table->addRow(array(
                    $k == $priorityPos ? $priority : '', $fix['name'], $fix['hash'], $fix['time']
                ));
                $count++;
            }
            $table->addRow(array());
        }
        $table->addRow(array(
            '----------', '---------------------', '----------', '----------'
        ));
        $table->addRow(array(
            '', 'Total: '.$count, '', $this->console->formatTime($time)
        ));
        $this->console
            ->printLine('List of migrations:')
            ->printLine($table->getTable());
    }
}
