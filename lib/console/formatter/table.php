<?php

namespace WS\ReduceMigrations\Console\Formatter;


use WS\ReduceMigrations\Console\Console;

class Table {

    private $rows;
    private $title;
    private $console;

    /**
     * Table constructor.
     *
     * @param string $title
     * @param Console $console
     */
    public function __construct($title, $console) {
        $this->title = $title;
        $this->console = $console;
    }

    /**
     * @param $row
     * @param $color
     */
    public function addColorRow($row, $color) {
        $currentRow = array();
        foreach ($row as $item) {
            $currentRow[] = $this->console->colorize($item, $color);
        }
        $this->rows[] = $currentRow;
    }

    public function addRow() {
        $this->rows[] = func_get_args();
    }

    public function __toString() {
        $result = '';
        $result .= $this->title . "\n";
        $maxLen = array();
        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                if (!$maxLen[$index]) {
                    $maxLen[$index] = iconv_strlen($value);
                }
                if ($maxLen[$index] < iconv_strlen($value)) {
                    $maxLen[$index] = iconv_strlen($value);
                }
            }
        }

        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                $result .= mb_str_pad($value, $maxLen[$index] + 3);
            }
            $result .= "\n";
        }

        return $result;
    }
}

function mb_str_pad($input, $pad_length, $pad_string=' ', $pad_type=STR_PAD_RIGHT) {
    if (function_exists('mb_strlen')) {
        $diff = strlen($input) - mb_strlen($input);
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }
    return str_pad($input, $pad_length, $pad_string, $pad_type);
}