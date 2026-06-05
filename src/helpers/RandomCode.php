<?php

namespace mmerlijn\msgHl7\helpers;

class RandomCode
{
    private string $valid = "0123456789ABCDEFGHIJKLMNOPQRSTVWXYZ" {
        set {
            $this->valid = $value;
        }
    }

    public function __construct(private int $length = 16)
    {
        return $this;
    }
    public function generate(?int $length = null): string
    {
        if($length){
             $this->length = $length;
        }
        $code="";
        for($i = 0; $i < $this->length; $i++){
            $code .= $this->valid[mt_rand(0,strlen($this->valid)-1)];
        }
        return $code;
    }
}