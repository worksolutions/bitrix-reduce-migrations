<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;

class LastCommand extends BaseCommand {

    public function execute($callback = false) {
        $lastSetupLog = \WS\ReduceMigrations\Module::getInstance()->getLastSetupLog();
        if (!$lastSetupLog) {
            $this->console
                ->printLine("Nothing to show.");
            return;
        }
        $appliedFixes = array();
        $errorFixes = array();

        foreach ($lastSetupLog->getAppliedLogs() as $appliedLog) {
            !$appliedLog->success && $errorFixes[] = $appliedLog;
            $appliedLog->success && $appliedFixes[$appliedLog->description]++;
        }
        foreach ($appliedFixes as $fixName => $fixCount) {
            $this->console
                ->printLine($fixName . ($fixCount > 1 ? "[$fixCount]" : ""), Console::OUTPUT_SUCCESS);
        }
        /** @var \WS\ReduceMigrations\Entities\AppliedChangesLogModel $errorApply */
        foreach ($errorFixes as $errorApply) {
            $errorData = \WS\ReduceMigrations\jsonToArray($errorApply->description) ?: $errorApply->description;
            if (is_scalar($errorData)) {
                $this->console
                    ->printLine($errorData, Console::OUTPUT_ERROR);
            }
            if (is_array($errorData)) {
                $this->console
                    ->printLine($errorData['message'], Console::OUTPUT_ERROR);
            }

        }
    }
}
