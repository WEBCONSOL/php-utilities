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
}