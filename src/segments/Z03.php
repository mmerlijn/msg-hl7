<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class Z03 extends Segment implements SegmentInterface
{
    public string $name = "Z03";

    public function getMsg(Msg $msg): Msg
    {
        $req_ind = count($msg->order->requests)-1;
        if ($req_ind < 0) {
            $msg->order->addRequest();
            $req_ind = 0;
        }
        $sp_i = count($msg->order->requests[$req_ind]->specimens)-1;
        if ($sp_i < 0) {
            $msg->order->requests[$req_ind]->addSpecimen();
            $sp_i = 0;
        }
        $msg->order->requests[$req_ind]->specimens[$sp_i]->location = $this->getData(1);
        return $msg;
    }
    public function setMsg(Msg $msg): self
    {
        return $this->setData($msg->order->getSpecimenByTestcode("BCBB")?->location, 1);
    }
}