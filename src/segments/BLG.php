<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class BLG extends Segment implements SegmentInterface
{
    public string $name = "BLG";

    public function setMsg(Msg $msg): self
    {
        $this->setData("CH", 2);
        $this->msgSegmentSetter($msg);
        return $this;
    }


    public function validate(): void
    {
    }
}