<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

class OBX extends Segment implements SegmentInterface
{

    public function getMsg(Msg $msg): Msg
    {

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {

    }
}