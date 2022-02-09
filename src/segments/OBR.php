<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Request;

class OBR extends Segment implements SegmentInterface
{
    public string $name = "OBR";

    public function getMsg(Msg $msg): Msg
    {
        $msg->order->addRequest(
            new Request(
                test_code: $this->getData(4),
                test_name: $this->getData(4, 0, 1),
                test_source: $this->getData(4, 0, 2)
            ));
        if (!$msg->order->request_nr) {
            $msg->order->request_nr = $this->getData(2);
        }
        $msg->order->where = OrderWhereEnum::set($this->getData(11));

        $msg->order->requester->agbcode = $this->getData(16);
        if (!$msg->order->requester->name->name) {
            $msg->order->requester->setName(
                new Name(
                    initials: $this->getData(16, 0, 2),
                    name: $this->getData(16, 0, 1)
                ));
            $msg->order->requester->source = $this->getData(16, 0, 8);
        }
        return $msg;
    }

    public function setRequest(Msg $msg, int $request_key): self
    {
        $this->setData($request_key + 1, 1);
        $this->setData($msg->order->request_nr, 2);
        $this->setData($msg->order->requests[$request_key]->test_code, 4);
        $this->setData($msg->order->requests[$request_key]->test_name, 4, 0, 1);
        $this->setData($msg->order->requests[$request_key]->test_source ?: "99zdl", 4, 0, 2);

        $this->setData($msg->order->where->getHl7(), 11);

        $this->setData($msg->order->requester->agbcode, 16);
        $this->setData($msg->order->requester->name->getLastnames(), 16, 0, 1);
        $this->setData($msg->order->requester->name->initials, 16, 0, 2);
        $this->setData($msg->order->requester->source, 16, 0, 8);
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "request_id" => $this->data[1][0][0][0] ?? "",
            "request_nr" => $this->data[2][0][0][0] ?? "",
            "request_identifier" => $this->data[4][0][0][0] ?? "",
            "request_where" => $this->data[11][0][0][0] ?? "",
        ], [
            "request_id" => 'required|numeric',
            "request_nr" => 'required',
            "request_identifier" => 'required',
            "request_where" => 'required',
        ], [
            "request_id" => '@ OBR[1][0][0][0] debug OBR',
            "request_nr" => '@ OBR[2][0][0][0] set/adjust $msg->order->request_nr',
            "request_identifier" => '@ OBR[4][0][0][0] set/adjust $msg->order->requests[..]->test_code',
            "request_where" => '@ OBR[11][0][0][0] set/adjust $msg->order->where',
        ]);
    }
}