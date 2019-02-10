<?php

namespace WC\Utilities;

class DateTimeFormat
{
    private static $utc = 'Etc/UTC';
    private static $timezone;

    private function __construct() { }

    public static function setup() {if (!self::$timezone) {self::$timezone = new \DateTimeZone(self::$utc);}}

    public static function getFormatISO8601($datetime='now') {
        self::setup();
        $date = new \Date($datetime);
        $date->setTimezone(self::$timezone);
        return $date->toISO8601(true);
    }

    public static function getFormatSql($datetime='now') {
        self::setup();
        $date = new Date($datetime);
        $date->setTimezone(self::$timezone);
        return $date->toSql(true);
    }

    public static function getFormatUnix($datetime='now') {
        self::setup();
        $date = new Date($datetime);
        $date->setTimezone(self::$timezone);
        return $date->toUnix();
    }

    public static function getSqlFormat() {return Date::$format;}

    public static function getStandardFormatString() {return "Y-m-d\TH:i:s";}

    public static function RFC3339ByTimestamp(int $timestamp): string {
        $date = date('Ymd\THisZ', $timestamp);
        $matches = array();
        if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
            $date .= $matches[1].$matches[2].':'.$matches[3];
        } else {
            $date .= 'Z';
        }
        return $date;
    }

    public static function RFC3339ByDateTime(string $date): string {
        return self::RFC3339ByTimestamp(strtotime($date));
    }
}