<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\TestCode;

class PV2 extends Segment implements SegmentInterface
{
    public string $name = "PV2";

    public function getMsg(Msg $msg): Msg
    {
        $msg->order->admit_reason = new TestCode(
            code:$this->getData(3),
            value: $this->getData(3, 0, 1)
        );
        return $msg;
    }

    public function setMsg(Msg $msg): self
    {
        if ($msg->order->admit_reason->code || $msg->order->admit_reason->value) {
            $this->setData($msg->order->admit_reason->code, 3);
            $this->setData($msg->order->admit_reason->value, 3, 0, 1);
            $this->setData($msg->default_source, 3, 0, 2);
        }
        return $this;
    }
}