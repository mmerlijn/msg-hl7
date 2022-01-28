<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class IN1 extends Segment implements SegmentInterface
{

    public function getMsg(Msg $msg): Msg
    {
        $msg->patient->setInsurance(new Insurance(
            company_name: $this->getData(4),
            policy_nr: $this->getData(36),
            uzovi: $this->getData(3),
        ));
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        if ($msg->patient->insurance) {
            $this->setData($msg->patient->insurance->policy_nr, 36);
            $this->setData($msg->patient->insurance->company_name, 4);
            if ($msg->patient->insurance->uzovi) {
                $this->setData("UZOVI", 3, 0, 4);
                $this->setData("VEKTIS", 3, 0, 3);
                $this->setData($msg->patient->insurance->uzovi, 3);
            }

        }
    }
}