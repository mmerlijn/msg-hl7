<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;

class ORC extends Segment implements SegmentInterface
{

    public function getMsg(Msg $msg): Msg
    {
        //order controe
        switch ($this->getData(1)) {
            case "NW":
                $msg->order->control = "NEW";
                break;
            case "CA":
                $msg->order->control = "CANCEL";
                break;
            case "XO":
                $msg->order->control = "CHANGE";
                break;
        }
        //requestnr
        $msg->order->request_nr = $this->getData(2);
        if (!$msg->order->request_nr) {
            $msg->order->request_nr = $this->getData(4);
        }
        //priority
        $msg->order->priority = ($this->getData(7, 0, 5) == "R") ? false : true;
        //transaction datetime
        $msg->order->dt_of_request = Carbon::createFromFormat("YmdHisO", $this->getData(9));
        //ordering provider
        $msg->order->requester->agbcode = $this->getData(12);
        $msg->order->requester->setName(new Name(name: $this->getData(12, 0, 1), initials: $this->getData(12, 0, 2)));
        $msg->order->requester->source = $this->getData(12, 0, 8);
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        //order controle
        if ($msg->order->control == "CANCEL") {
            $this->setData("CA", 1);
        } elseif ($msg->order->control == "CHANGE") {
            $this->setData("XO", 1);
        } else {
            $this->setData("NW", 1);
        }
        //requestnr
        $this->setData($msg->order->request_nr, 2);
        $this->setData($msg->order->request_nr, 4);
        //priority
        $this->setData($msg->order->priority ? "C" : "R", 7, 0, 5);
        //transaction datetime
        $this->setData($msg->order->dt_of_request?->format("YmdHisO"), 9);
        //requester (ordering provider)
        $this->setData($msg->order->requester->agbcode, 12);
        $this->setData($msg->order->requester->name->getLastnames(), 12, 0, 1);
        $this->setData($msg->order->requester->name->initials, 12, 0, 2);
        $this->setData($msg->order->requester->source, 12, 0, 8);


    }
}