<?php

namespace mmerlijn\msgHl7\helpers;

class Hl7Formatter
{

    public static function encode($data):string
    {
        //alle line breaks omzetten naar \.br\
        $data = preg_replace('/\r\n|\r|\n/', '\\.br\\', $data);
        if (is_array($data)) {
            $data = array_map(function ($item) {
                return self::encode($item);
            }, $data);
            return implode("\\.br\\", $data);
        }
        return (string)$data;
    }
}