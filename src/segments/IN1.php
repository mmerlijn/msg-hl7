<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class IN1 extends Segment implements SegmentInterface
{
    public string $name = "IN1";

    public function getMsg(Msg $msg): Msg
    {
        $msg->patient->setInsurance(new Insurance(
            uzovi: $this->getData(3),
            policy_nr: $this->getData(36),
            company_name: $this->getData(4),
        ));
        $this->msgSegmentGetter($msg);
        return $msg;
    }

    public function setMsg(Msg $msg): self
    {
        $this->setData("1", 1);
        $this->setData("null", 2, 0, 1);
        if ($msg->patient->insurance) {
            $this->setData($msg->patient->insurance->policy_nr, 36);
            $this->setData($msg->patient->insurance->company_name, 4);
            if ($msg->patient->insurance->uzovi) {
                $this->setData("UZOVI", 3, 0, 4);
                $this->setData("VEKTIS", 3, 0, 3);
                $this->setData($msg->patient->insurance->uzovi, 3);
            }
        }
        $this->msgSegmentSetter($msg);
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "id" => $this->data[1][0][0][0] ?? "",
            //       "insurance_company" => $this->data[2][0][0][0] ?? "",
        ], [
            "id" => 'required|numeric',
            //       "insurance_company" => 'required',
        ], [
            "id" => '@ IN1[1][0][0][0] bugfix IN1 segment',
            //       "insurance_company" => '@ IN1[2][0][0][0] set/adjust  $msg->patient->insurance',
        ]);
    }
}