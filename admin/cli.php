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

global $USER;
$USER->Authorize(1);

$module = \WS\ReduceMigrations\Module::getInstance();
$console = new Console($argv);

$getShowProgress = function () use ($console) {
    $counter = new \WS\ReduceMigrations\Console\RuntimeCounter();
    return function ($data, $type) use ($console, $counter) {
        if ($type == 'setCount') {
            $counter->migrationCount = $data;
            $counter->start = microtime(true);
        }
        if ($type == 'start') {
            $counter->migrationNumber++;
            $console->printLine("{$data['name']}({$counter->migrationNumber}/$counter->migrationCount)", Console::OUTPUT_PROGRESS);
        }
        if ($type == 'end') {
            /**@var \WS\ReduceMigrations\Entities\AppliedChangesLogModel $log */
            $log = $data['log'];
            $time = round($data['time'], 2);
            $message = '';
            if (!empty($data['error'])) {
                $message .= $data['error'] . '. ';
            }
            $overallTime = round(microtime(true) - $counter->start, 2);
            $message .= "$time sec ($overallTime sec)";
            $console->printLine($message, $log->success ? Console::OUTPUT_SUCCESS: Console::OUTPUT_ERROR);
        }
    };
};
try {
    $console->printLine("");
    $command = $console->getCommand();
    $command->execute($getShowProgress());
} catch (\WS\ReduceMigrations\Console\ConsoleException $e) {
    $console->printLine($e->getMessage());
}

