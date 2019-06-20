<?php

namespace WC\Utilities;

class HBS
{
    const ATTR_NAME = 'data-hbs';

    private function __construct(){}

    public static function renderTemplate(string $name, string $str) {
        echo '<script id="entry-template" type="text/x-handlebars-template" '.ATTR_NAME.'="'.$name.'">';
        if (is_file($str)) {include $str;} else {echo $str;}
        echo '<'.'/script>';
    }

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
                self::renderTemplate($filename, $item);
            }
        }
    }

    public static function loadTemplatesRecursively(string $root, bool $reload=false) {
        $list = glob($root . '/*');
        if (!empty($list)) {
            foreach ($list as $item) {
                if (is_dir($item)) {
                    self::loadTemplatesRecursively($item, true);
                }
                else if (is_file($item) && pathinfo($item, PATHINFO_EXTENSION) === 'hbs') {
                    if ($reload) {
                        $filename = pathinfo($item, PATHINFO_DIRNAME).'-'.pathinfo($item, PATHINFO_FILENAME);
                    }
                    else {
                        $filename = pathinfo($item, PATHINFO_FILENAME);
                    }
                    self::renderTemplate($filename, $item);
                }
            }
        }
    }

    public static function loadTemplatesList(array $list) {
        if (!empty($list)) {
            foreach ($list as $file) {
                $tmpl = pathinfo($file, PATHINFO_FILENAME);
                self::renderTemplate($tmpl, $file);
            }
        }
    }
}