<?php

use WS\ReduceMigrations\Console\Console;

$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define('CHK_EVENT', true);

@set_time_limit(0);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once(__DIR__."/../include.php");
require_once(__DIR__."/../prolog.php");


$module = \WS\ReduceMigrations\Module::getInstance();
$console = new Console($argv);

$fCompanyLabel = function () use ($console) {
    $console
        ->printLine("Migrations module for CMS Bitrix. Worksolutions company https://worksolutions.ru \n");
};

$getShowProgress = function () use ($console) {
    $counter = new \WS\ReduceMigrations\Console\RuntimeCounter();
    return function ($data, $type) use ($console, $counter) {
        if ($type == 'setCount') {
            $counter->migrationCount = $data;
            $counter->start = microtime(true);
        }
        if ($type == 'start') {
            $counter->migrationNumber++;
            $console->printLine(sprintf(
                '%s (%s/%s)',
                $console->colorize($data['name'], Console::OUTPUT_PROGRESS),
                $counter->migrationNumber, $counter->migrationCount
            ));
        }
        if ($type == 'end') {
            /**@var \WS\ReduceMigrations\Entities\AppliedChangesLogModel $log */
            $log = $data['log'];
            $time = round($data['time'], 2);
            $message = '';
            if (!empty($data['error'])) {
                $message .= '  ' . $data['error'];
            }
            $overallTime = round(microtime(true) - $counter->start, 2);
            $message .= sprintf('  - %s (%s)', $console->formatTime($time), $console->formatTime($overallTime));
            if ($log->isSkipped()) {
                $message = '  - skipped';
            }
            $console->printLine($message, $log->isFailed() ? Console::OUTPUT_ERROR : Console::OUTPUT_SUCCESS);
            $console->printLine("");
        }
    };
};
try {
    $console->printLine('');
    $command = $console->getCommand();
    $command->execute($getShowProgress());
    $fCompanyLabel();
} catch (\WS\ReduceMigrations\Console\ConsoleException $e) {
    $console->printLine($e->getMessage());
    $fCompanyLabel();
}
