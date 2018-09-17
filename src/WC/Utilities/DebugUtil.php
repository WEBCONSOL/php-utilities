<?php

namespace WC\Utilities;

class DebugUtil
{
    public static $data = array();

    public static function print() {
        $a = func_get_args();
        if (is_array($a) && sizeof($a)) {
            $printOnly = true;
            if (is_bool($a[sizeof($a)-1])) {
                $printOnly = $a[sizeof($a)-1];
                unset($a[sizeof($a)-1]);
            }
            if (!$printOnly) {echo '<pre>';}
            foreach ($a as $v) {
                if (is_string($v)) { echo $v; }
                else { print_r($v); }
                echo "\n";
            }
            if(!$printOnly) die('</pre>');
        }
    }
}