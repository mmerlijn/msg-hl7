<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

class PV1 extends Segment implements SegmentInterface
{
    public string $name = "PV1";

    public function getMsg(Msg $msg): Msg
    {

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {

    }
}