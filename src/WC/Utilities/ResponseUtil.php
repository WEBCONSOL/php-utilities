<?php

namespace WC\Utilities;

class ResponseUtil
{
    private function __construct(){}

    public static function withJSON(array $response=array()) {
        header('Content-Type: application/json; charset=UTF-8');
        if (!empty($response)) {
            die(json_encode($response));
        }
    }
}