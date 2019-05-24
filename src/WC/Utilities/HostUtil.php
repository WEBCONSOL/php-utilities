<?php

namespace WC\Utilities;

final class HostUtil
{
    private static $EZPZ_CDN = 'https://%scdn.ezpz.solutions';

    public static function getPfx(): string {
        $parts = explode('-', $_SERVER['HTTP_HOST']);
        if (sizeof($parts) > 1) {
            return $parts[0];
        }
        else {
            if (StringUtil::startsWith($_SERVER['HTTP_HOST'], 'local')) {
                return 'local';
            }
            if (StringUtil::startsWith($_SERVER['HTTP_HOST'], 'dev')) {
                return 'dev';
            }
            if (StringUtil::startsWith($_SERVER['HTTP_HOST'], 'qa')) {
                return 'qa';
            }
            if (StringUtil::startsWith($_SERVER['HTTP_HOST'], 'stage')) {
                return 'stage';
            }
            return '';
        }
    }

    public static function ezpzCDN(): string {
        $hostPfx = self::getPfx();
        return sprintf(self::$EZPZ_CDN, $hostPfx.($hostPfx?'-':''));
    }
}