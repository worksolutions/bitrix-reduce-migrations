<?php
/**
 * Created by JetBrains PhpStorm.
 * User: under5
 * Date: 08.12.14
 * Time: 12:05
 * To change this template use File | Settings | File Templates.
 */

namespace WS\ReduceMigrations\factories;


use Bitrix\Main\Type\DateTime;

class DateTimeFactory {

    static public function createBase($time = null) {
        return new \DateTime($time, self::timeZone());
    }

    /**
     * @param \DateTime $date
     * @return \Bitrix\Main\Type\DateTime
     */

    public static function createBitrix(\DateTime $date) {
        $format = 'd.m.Y H:i:s';
        $object = new DateTime($date->format($format), $format, self::timeZone());
        return $object;
    }

    /**
     * @return \DateTimeZone
     */
    public static function timeZone() {
        try {
            $obj = new \DateTime();
            return $obj->getTimezone();
        } catch (\Exception $e) {
            $timezoneIdentifier = 'Europe/Moscow';
            date_default_timezone_set($timezoneIdentifier);
            return new \DateTimeZone($timezoneIdentifier);
        }
    }
}