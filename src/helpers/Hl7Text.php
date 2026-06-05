<?php

namespace mmerlijn\msgHl7\helpers;

class Hl7Text
{
    public mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data ?? '';
        return $this;
    }

    public function encode(): string
    {
        if (!is_array($this->data)) {
            $this->data = [$this->data];
        }
        $this->data = array_map(function ($item) {
            $item = preg_replace('/\\\/', '\\E\\', $item); //backslash
            $item = preg_replace('/\|/', '\\F\\', $item); //field separator |
            $item = preg_replace('/~/', '\\R\\', $item); //repetition separator ~
            $item = preg_replace('/\^/', '\\S\\', $item); //component separator ^
            $item = preg_replace('/&/', '\\T\\', $item); //subcomponent separator &
            return preg_replace('/\r\n|\r|\n/', '\\.br\\', $item);
        }, $this->data);
        $this->data = implode("\\.br\\", $this->data);
        return trim($this->data);
    }

    public function decode(): string
    {
        $this->data = preg_replace('/\\\E\\\/', '/\\\/', $this->data); //backslash
        $this->data = preg_replace( '/\r/',PHP_EOL, $this->data); //carriage return
        $this->data = preg_replace('/\\\F\\\/', '|', $this->data); //field separator |
        $this->data = preg_replace('/\\\R\\\/', '~', $this->data); //repetition separator ~
        $this->data = preg_replace('/\\\S\\\/', '^', $this->data); //component separator ^
        $this->data = preg_replace('/\\\T\\\/', '&', $this->data); //subcomponent separator &
        $this->data = preg_replace('/\\\.*\\\/', '', $this->data); //diverse tekens
        $this->data = preg_replace('/\s+/', ' ', $this->data); //multiple spaces to 1
        $this->data = str_replace('\\.br\\', PHP_EOL, $this->data);
        return trim($this->data);
    }


}