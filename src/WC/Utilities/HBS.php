<?php

namespace WC\Utilities;

class HBS
{
    private function __construct(){}

    public static function loadTemplates(string $root) {
        $list = [];
        if (StringUtil::endsWith($root, "/*.hbs")) {
            $list = glob($root);
        }
        else if (StringUtil::endsWith($root, ".hbs") && is_file($root)) {
            $list = [$root];
        }
        else if (is_dir($root)) {
            $list = glob($root . '/*.hbs');
        }
        if (!empty($list)) {
            foreach ($list as $item) {
                $filename = pathinfo($item, PATHINFO_FILENAME);
                echo '<script id="entry-template" type="text/x-handlebars-template" data-hbs="'.$filename.'">';
                include $item;
                echo '<'.'/script>';
            }
        }
    }
}