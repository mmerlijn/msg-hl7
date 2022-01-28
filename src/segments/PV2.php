<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

class PV2 extends Segment implements SegmentInterface
{

    public function getMsg(Msg $msg): Msg
    {
        $msg->order->admit_reason_code = $this->getData(3);
        $msg->order->admit_reason_name = $this->getData(3, 0, 1);
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        if ($msg->order->admit_reason_code) {
            $this->setData($msg->order->admit_reason_code, 3);
            $this->setData($msg->order->admit_reason_name, 3, 0, 1);
            $this->setData("99zda", 3, 0, 2);
        }
    }
}