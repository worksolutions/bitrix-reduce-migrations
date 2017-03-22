<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Tests\Cases;


class ErrorException extends \Exception
{

    private $_dumpedValue;

    public function setDump($value) {
        $this->_dumpedValue = $value;
    }

    public function getDump() {
        return $this->_dumpedValue;
    }
}