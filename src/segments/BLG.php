<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class BLG extends Segment implements SegmentInterface
{
    public string $name = "BLG";

    public function getMsg(Msg $msg): Msg
    {
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
    }

    public function validate(): void
    {
    }
}