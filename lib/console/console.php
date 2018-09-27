<?php

namespace WS\ReduceMigrations\Console;

use WS\ReduceMigrations\Console\Command\ApplyCommand;
use WS\ReduceMigrations\Console\Command\BaseCommand;
use WS\ReduceMigrations\Console\Command\CreateScenarioCommand;
use WS\ReduceMigrations\Console\Command\HelpCommand;
use WS\ReduceMigrations\Console\Command\History;
use WS\ReduceMigrations\Console\Command\ListCommand;
use WS\ReduceMigrations\Console\Command\RollbackCommand;
use WS\ReduceMigrations\Console\Formatter\Output;
use WS\ReduceMigrations\MessageOutputInterface;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\TimeFormatter;

class Console implements MessageOutputInterface {
    const OUTPUT_ERROR = 'error';
    const OUTPUT_PROGRESS = 'progress';
    const OUTPUT_SUCCESS = 'success';
    /**
     * @var resource
     */
    private $out;
    private $action;
    /** @var  Output */
    private $successOutput;
    /** @var  Output */
    private $errorOutput;
    /** @var  Output */
    private $progressOutput;
    /** @var  Output */
    private $defaultOutput;
    /** @var TimeFormatter  */
    private $timeFormatter;

    public function __construct($args) {
        global $APPLICATION;
        $APPLICATION->ConvertCharsetArray($args, "UTF-8", LANG_CHARSET);
        $this->out = fopen('php://stdout', 'w');
        array_shift($args);
        $this->params = $args;
        $this->action = isset($this->params[0]) ? $this->params[0] : '--help';
        foreach ($args as $arg) {
            if ($arg == '--help') {
                $this->action = '--help';
                $index = array_search($this->action, $this->params);
                if ($index !== false) {
                    unset($this->params[$index]);
                }
                array_unshift($this->params, $this->action);
            }
        }
        $this->successOutput = new Output('green');
        $this->errorOutput = new Output('red');
        $this->progressOutput = new Output('yellow');
        $this->defaultOutput = new Output();
        $this->timeFormatter = new TimeFormatter(array(
            'minutes' => 'min',
            'seconds' => 'sec'
        ));

        Module::getInstance()->setScenariosMessageOutput($this);
    }

    /**
     * @param $str
     * @param $type
     * @return Console
     */
    public function printLine($str, $type = false) {
        global $APPLICATION;
        $str = $APPLICATION->ConvertCharset($str, LANG_CHARSET, "UTF-8");
        $str = $this->colorize($str, $type);
        fwrite($this->out, $str . "\n");
        return $this;
    }

    public function println($str) {
        return $this->printInProgress($str);
    }

    /**
     * @param $str
     * @return Console
     */
    public function printError($str) {
        return $this->printLine($str, self::OUTPUT_ERROR);
    }

    /**
     * @param $str
     * @return Console
     */
    public function printInProgress($str) {
        return $this->printLine($str, self::OUTPUT_PROGRESS);
    }

    /**
     * @param $str
     * @return Console
     */
    public function printSuccess($str) {
        return $this->printLine($str, self::OUTPUT_SUCCESS);
    }

    public function colorize($str, $type = false) {
        if ($type) {
            $str = $this->getOutput($type)->colorize($str);
        }
        return $str;
    }

    public function readLine() {
        return trim(fgets(STDIN));
    }

    /**
     * @return BaseCommand
     * @throws ConsoleException
     */
    public function getCommand() {
        $commands = array(
            '--help' => HelpCommand::className(),
            'list' => ListCommand::className(),
            'apply' => ApplyCommand::className(),
            'rollback' => RollbackCommand::className(),
            'history' => History::className(),
            'createScenario' => CreateScenarioCommand::className(),
            'create' => CreateScenarioCommand::className(),
        );
        if (!$commands[$this->action]) {
            throw new ConsoleException("Action `{$this->action}` is not supported");
        }
        $params = $this->prepareParams($this->params);
        return new $commands[$this->action]($this, $params);
    }

    /**
     * @param $type
     * @return Output
     */
    private function getOutput($type) {
        switch ($type) {
            case 'success':
                return $this->successOutput;
                break;
            case 'error':
                return $this->errorOutput;
                break;
            case 'progress':
                return $this->progressOutput;
                break;
            default:;
        }
        return $this->defaultOutput;
    }

    /**
     * @param $params
     *
     * @return array
     */
    private function prepareParams($params) {
        array_shift($params);
        $namedParams = array();
        $positionalParams = array();
        foreach ($params as $param) {
            if (strpos($param, '-') === 0) {
                $param = explode('=', $param);
                $namedParams[$param[0]] = $param[1] ?: true;
            } else {
                $positionalParams[] = $param;
            }
        }
        $params = array_merge($positionalParams, $namedParams);

        return $params;
    }

    public function formatTime($time) {
        return $this->timeFormatter->format($time);
    }
}
