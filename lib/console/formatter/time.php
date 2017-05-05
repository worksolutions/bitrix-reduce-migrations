<?php

namespace WS\ReduceMigrations\Console\Formatter;


class Time {
    const SECONDS_PRECISION = 3;
    const MINUTES_PRECISION = 2;
    const SECONDS_THRESHOLD_VALUE = 100;
    const SECONDS_IN_MINUTE = 60;

    public function format($initialTime) {
        $time = round($initialTime, self::SECONDS_PRECISION);
        if ($time >= self::SECONDS_THRESHOLD_VALUE) {
            $time = round($time / self::SECONDS_IN_MINUTE, self::MINUTES_PRECISION);
            $time = sprintf('%s min', $time);
        } else {
            $time = sprintf('%s sec', $time);
        }
        return $time;
    }
}