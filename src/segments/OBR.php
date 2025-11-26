<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Contact;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\TestCode;

class OBR extends Segment implements SegmentInterface
{
    public string $name = "OBR";

    protected array $date_fields = [
        "7.0.0" => 'datetime',
        "20.0.0" => 'datetime',
    ];

    public function getMsg(Msg $msg): Msg
    {
        $request = new Request(
            test: new TestCode(
                code: $this->getData(4),
                value: $this->getData(4, 0, 1),
                source: $this->getData(4, 0, 2),
                a_code: $this->getData(4, 0, 3),
                a_value: $this->getData(4, 0, 4),
                a_source: $this->getData(4, 0, 5),
            ),
            other_test: new TestCode(
                code: $this->getData(15),
                value: $this->getData(15, 0, 0, 1),
                source: $this->getData(15, 0, 0, 2),
            ),
            id: $this->getData(2),
        );

        $id = $this->getData(1);
        if (!isset($msg->order->requests[$id - 1])) {
            $msg->order->addRequest($request);
        } else {
            $msg->order->requests[$id - 1] = $request;
        }
        if (!$msg->order->request_nr) {
            $msg->order->request_nr = $this->getData(2);
        }
        if (in_array($this->getData(5), ["C", "S", "CITO"])) {
            $msg->order->priority = true;
        } elseif (in_array($this->getData(5), ["R"])) {
            $msg->order->priority = false;
        }

        $msg->order->observation_at = $this->getDate(7);

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
        $msg->order->start_date = $this->getDate(20);

        if ($this->getData(28)) { //copy_to
            $msg->order->copy_to = new Contact(
                agbcode: $this->getData(28),
                name: new Name(
                    initials: $this->getData(28, 0, 2),
                    name: $this->getData(28, 0, 1),
                ),
                source: $this->getData(28, 0, 8),
            );
        }
        //$this->msgSegmentGetter($msg,count($msg->order->requests)-1);
        return $msg;
    }

    //for testing purposes only set first request
    public function setMsg(Msg $msg): self
    {
        return $this->setRequest($msg, 0);

    }

    public function setRequest(Msg $msg, int $request_key): self
    {
        $this->setData($request_key + 1, 1);
        $this->setData($msg->order->requests[$request_key]->id ?: $msg->order->request_nr, 2);

        $this->setData($msg->order->requests[$request_key]->test->code, 4);
        $this->setData($msg->order->requests[$request_key]->test->value, 4, 0, 1);
        $this->setData($msg->order->requests[$request_key]->test->source ?: $msg->default_source, 4, 0, 2);
        $this->setData($msg->order->requests[$request_key]->test->a_code, 4, 0, 3);
        $this->setData($msg->order->requests[$request_key]->test->a_value, 4, 0, 4);
        $this->setData($msg->order->requests[$request_key]->test->a_source, 4, 0, 5);
        //priority
        if ($msg->order->priority !== null) {
            $this->setData($msg->order->priority ? "C" : "R", 5);
        }
        $this->setDate($msg->order->observation_at, 7);
        $this->setData($msg->order->where->getHl7(), 11);

        $this->setData($msg->order->requester->agbcode, 16);
        $this->setData($msg->order->requester->name->getLastnames(), 16, 0, 1);
        $this->setData($msg->order->requester->name->initials, 16, 0, 2);
        $this->setData($msg->order->requester->source, 16, 0, 8);

        if ($msg->order->requests[$request_key]->other_test->value) {
            $this->setData($msg->order->requests[$request_key]->other_test->code, 15);
            $this->setData($msg->order->requests[$request_key]->other_test->value, 15, 0, 0, 1);
            $this->setData($msg->order->requests[$request_key]->other_test->source ?: $msg->default_source, 15, 0, 0, 2);
        }
        if ($msg->order->start_date) {
            $this->setDate($msg->order->start_date, 20);
        }
        //set copy_to
        if ($msg->order->copy_to?->agbcode) {
            $this->setData($msg->order->copy_to->agbcode, 28);
            $this->setData($msg->order->copy_to->name->getLastnames(), 28, 0, 1);
            $this->setData($msg->order->copy_to->name->initials, 28, 0, 2);
            $this->setData($msg->order->copy_to->source, 28, 0, 8);
        }
        $this->msgSegmentSetter($msg, $request_key);
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