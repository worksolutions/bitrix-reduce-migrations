<?php

namespace WS\ReduceMigrations\Console\Command;

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
        foreach ($this->module->getNotAppliedScenarios() as $priority => $list) {
            foreach ($list as $notAppliedScenario) {
                $this->registerFix($notAppliedScenario::name());
                $has = true;
            }
        }
        !$has && $this->console->printLine("Nothing to apply");
        $has && $this->printRegisteredFixes();
    }

    private function registerFix($name) {
        if ($name == 'Reference fix') {
            $name = $this->localization->message('common.reference-fix');
        }
        $this->registeredFixes[$name]++;
    }

    private function printRegisteredFixes() {
        foreach ($this->registeredFixes as $name => $count) {
            $count = $count > 1 ? '['.$count.']' : '';
            $this->console->printLine($name.' '.$count);
        }
    }


}
