<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations;


class Timer {

    private $time = 0;

    const TIME_PRECISION = 2;

    public function start() {
        $this->time = microtime(true);
    }

    public function stop() {
        $this->time = microtime(true) - $this->time;
    }

    public function getTime() {
        return round($this->time, self::TIME_PRECISION);
    }

    public function __toString() {
        return (string)$this->getTime();
    }
}