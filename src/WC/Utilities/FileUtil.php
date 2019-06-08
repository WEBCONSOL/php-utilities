<?php

namespace WC\Utilities;

class FileUtil
{
    public static function copyImageFromRemoteServer($url, $filename)
    {
        $copy = @copy($url, $filename);
        if ($copy && file_exists($filename)) {
            return $filename;
        }
        return false;
    }

    public static function getExtension($f): string {return pathinfo($f, PATHINFO_EXTENSION);}

    public static function fetchCache($dir, &$list) {
        $glob = glob($dir.'/*');
        foreach ($glob as $f) {
            if (is_dir($f)) {
                $list[] = $f;
                self::fetchCache($f, $list);
            }
            else {
                $list[] = $f;
            }
        }
    }
}