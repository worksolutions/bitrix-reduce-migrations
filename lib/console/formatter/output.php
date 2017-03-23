<?php

namespace WS\ReduceMigrations\Console\Formatter;


class Output {

    private $color;

    public function __construct($color = 'default') {
        $colors = $this->textColors();
        $this->color = isset($colors[$color]) ? $colors[$color] : $colors['default'];
    }

    public function textColors () {
        return array(
            'black' => 30,
            'red' => 31,
            'green' => 32,
            'yellow' => 33,
            'blue' => 34,
            'magenta' => 35,
            'cyan' => 36,
            'white' => 37,
            'default' => 0
        );
    }

    public function colorize($text) {
        return chr(27) . "[{$this->color}m" . $text . chr(27) . "[0m";
    }

}
