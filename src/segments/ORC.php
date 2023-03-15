<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;

class ORC extends Segment implements SegmentInterface
{
    public string $name = "ORC";

    public function getMsg(Msg $msg): Msg
    {
        //order controe
        $msg->order->control = OrderControlEnum::set($this->getData(1));
        //requestnr
        $msg->order->request_nr = $this->getData(2);
        if (!$msg->order->request_nr) {
            $msg->order->request_nr = $this->getData(4);
        }
        //priority

        $msg->order->priority = match ($this->getData(7, 0, 5)) {
            "C", "S", "CITO" => true,
            default => false,
        };
        //transaction datetime
        $msg->order->dt_of_request = $this->getDate(9);

        //ordering provider
        $msg->order->requester->agbcode = $this->getData(12);
        $msg->order->requester->setName(new Name(name: $this->getData(12, 0, 1), initials: $this->getData(12, 0, 2)));
        $msg->order->requester->source = $this->getData(12, 0, 8);
        $msg->order->requester->location = $this->getData(13);
        return $msg;
    }

    public function setOrder($msg): self
    {
        //order controle
        $this->setData($msg->order->control->getHl7(), 1);
        //requestnr
        $this->setData($msg->order->request_nr, 2);
        $this->setData($msg->order->request_nr, 4);
        //priority
        $this->setData($msg->order->priority ? "C" : "R", 7, 0, 5);
        //transaction datetime
        $this->setData($msg->order->dt_of_request?->format($this->datatime_format), 9);
        //requester (ordering provider)
        $this->setData($msg->order->requester->agbcode, 12);
        $this->setData($msg->order->requester->name->getLastnames(), 12, 0, 1);
        $this->setData($msg->order->requester->name->initials, 12, 0, 2);
        $this->setData($msg->order->requester->source, 12, 0, 8);
        $this->setData($msg->order->requester->location, 13);
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "order_controle" => $this->data[1][0][0][0] ?? "",
            "order_request_nr" => $this->data[2][0][0][0] ?? "",
            "order_request_nr2" => $this->data[2][0][0][0] ?? "",
            "order_priority" => $this->data[7][0][5][0] ?? "",
            "order_requester" => $this->data[12][0][1][0] ?? "",
        ], [
            "order_controle" => 'required',
            "order_request_nr" => 'required',
            "order_request_nr2" => 'required',
            "order_priority" => 'required',
            "order_requester" => 'required',
        ], [
            "order_controle" => '@ ORC[1][0][0][0] debug OBR',
            "order_request_nr" => '@ ORC[2][0][0][0] set/adjust $msg->order->request_nr',
            "order_request_nr2" => '@ ORC[4][0][0][0] set/adjust $msg->order->request_nr',
            "order_priority" => '@ ORC[7][0][5][0] set/adjust $msg->order->priority',
            "order_requester" => '@ ORC[12][0][1][0] set/adjust $msg->order->requester->name',
        ]);
    }
}