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
        if (!isset($msg->order->requests[(count($msg->order->requests) - 1)])) {
            $msg->order->addRequest();
        }
        if($this->getData(7,0,0)){ //tq7.1
            $msg->order->start_date = $this->getDate(7);
        }
        if($this->getData(8,0,0)){ //tq7.1
            $msg->order->end_date = $this->getDate(8);
        }
        if (in_array($this->getData(9),["C","CITO"])) {
            $msg->order->requests[(count($msg->order->requests) - 1)]->priority = true;
            $msg->order->requests[(count($msg->order->requests) - 1)]->cito = true;
            $msg->order->priority = true;
            $msg->order->cito = true;
        }elseif(in_array($this->getData(9),["S","A"])){
            $msg->order->requests[(count($msg->order->requests) - 1)]->priority = true;
            $msg->order->priority = true;
        } elseif ($this->getData(9) === "R") {
            $msg->order->requests[(count($msg->order->requests) - 1)]->priority = false;
            $msg->order->priority = false;
        }


        return $msg;
    }

    //for testing purposes only
    public function setMsg(Msg $msg): self
    {
        return $this->setRequest($msg, 0);
    }

    public function setRequest(Msg $msg, int $request_key): self
    {

        $this->setData($request_key + 1, 1);
        if($msg->order->start_date){
            $this->setDate($msg->order->start_date, 7); //tq7.1
        }
        if($msg->order->end_date){
            $this->setDate($msg->order->end_date, 8); //tq8.1
        }
        //priority
        if ($msg->order->requests[$request_key]->priority or $msg->order->priority) {
            $this->setData("A", 9);
            $this->setData("ASAP", 9,0,1);
        }elseif($msg->order->requests[$request_key]->cito or $msg->order->cito){
            $this->setData("C", 9);
            $this->setData("Callback", 9, 0, 1);
        } else {
            $this->setData("R", 9);
            $this->setData("Routine", 9, 0, 1);
        }
        $this->setData("HL70485", 9, 0, 2);
        $this->msgSegmentSetter($msg, $request_key);
        return $this;
    }

    public function validate(): void
    {
    }
}