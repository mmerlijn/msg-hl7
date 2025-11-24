<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

class PV1 extends Segment implements SegmentInterface
{
    public string $name = "PV1";


    public function setMsg(Msg $msg): self
    {
        $this->setData("1", 1);
        $this->setData("O", 2);
        $this->setData("V", 51);
        $this->msgSegmentSetter($msg);
        return $this;
    }
}