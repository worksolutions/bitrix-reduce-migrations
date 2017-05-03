<?php

namespace WS\ReduceMigrations\Console\Command;

use WS\ReduceMigrations\Console\Console;
use WS\ReduceMigrations\Module;

abstract class BaseCommand {
    const CONFIRM_WORD = 'yes';
    /** @var Console  */
    protected $console;
    /** @var Module  */
    protected $module;

    public function __construct(Console $console, $params) {
        $this->console = $console;
        $this->initParams($params);
        $this->module = Module::getInstance();
    }

    /**
     * @return string
     */
    static public function className() {
        return get_called_class();
    }
    protected function initParams($params) {}

    abstract public function execute($callback = false);

}
