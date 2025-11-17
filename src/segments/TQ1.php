<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class TQ1 extends Segment implements SegmentInterface
{
    public string $name = "TQ1";

    public function getMsg(Msg $msg): Msg
    {
        if(!isset($msg->order->requests[(count($msg->order->requests)-1)])){
            $msg->order->addRequest();
        }
        $msg->order->requests[(count($msg->order->requests)-1)]->priority = $this->getData(9) === "C" || $this->getData(9) === "S" || $this->getData(9) === "CITO";
        if($msg->order->requests[(count($msg->order->requests)-1)]->priority){
            $msg->order->priority = true;
        }
        return $msg;
    }

    //for testing purposes only
    public function setMsg(Msg $msg): self
    {
        return $this->setRequest( $msg, 0);
    }
    public function setRequest(Msg $msg, int $request_key): self
    {

        $this->setData($request_key + 1, 1);
        //priority
        if ($msg->order->requests[$request_key + 1]->priority or $msg->order->priority) {
            $this->setData("C", 9);
            //TODO fill
        } else {
            $this->setData("R", 9);
            $this->setData("Routine", 9,0,1);
            $this->setData("HL70485", 9,0,2);
        }
        return $this;
    }

    public function validate(): void
    {
    }
}