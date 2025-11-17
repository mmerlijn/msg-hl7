<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

class Undefined extends Segment implements SegmentInterface
{
    public string $name = "UNDEFINED";

    public function getMsg(Msg $msg): Msg
    {
        return $msg;
    }


    public function validate(): void
    {

    }
}