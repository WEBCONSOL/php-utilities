<?php

namespace WC\Utilities;

final class HostUtil
{
    private static $EZPZ_CDN = 'https://%scdn.ezpz.solutions';

    public static function isLocal(): bool {return StringUtil::startsWith($_SERVER['HTTP_HOST'], 'local');}
    public static function isDev(): bool {return StringUtil::startsWith($_SERVER['HTTP_HOST'], 'dev');}
    public static function isQA(): bool {return StringUtil::startsWith($_SERVER['HTTP_HOST'], 'qa');}
    public static function isStage(): bool {return StringUtil::startsWith($_SERVER['HTTP_HOST'], 'stage');}

    public static function getPfx(): string {
        $parts = explode('-', $_SERVER['HTTP_HOST']);
        if (sizeof($parts) > 1) {
            return $parts[0];
        }
        else {
            if (self::isLocal()) {return 'local';}
            if (self::isDev()) {return 'dev';}
            if (self::isQA()) {return 'qa';}
            if (self::isStage()) {return 'stage';}
            return '';
        }
    }

    public static function ezpzCDN(): string {
        $hostPfx = self::getPfx();
        return sprintf(self::$EZPZ_CDN, $hostPfx.($hostPfx?'-':''));
    }
}