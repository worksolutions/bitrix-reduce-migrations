<?php

namespace WS\ReduceMigrations;


class TimeFormatter {
    const SECONDS_PRECISION = 3;
    const MINUTES_PRECISION = 2;
    const SECONDS_THRESHOLD_VALUE = 100;
    const SECONDS_IN_MINUTE = 60;

    private $lang;

    /**
     * Time constructor.
     *
     * @param array $lang - ['minutes'=> 'min', 'seconds' => 'sec']
     */
    public function __construct($lang) {
        $this->lang = $lang;
    }

    public function format($initialTime) {
        $time = round($initialTime, self::SECONDS_PRECISION);
        if ($time >= self::SECONDS_THRESHOLD_VALUE) {
            $time = round($time / self::SECONDS_IN_MINUTE, self::MINUTES_PRECISION);
            $time = sprintf('%s %s', $time, $this->lang['minutes']);
        } else {
            $time = sprintf('%s %s', $time, $this->lang['seconds']);
        }
        return $time;
    }
}