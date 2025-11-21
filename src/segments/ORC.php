<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Organisation;
use mmerlijn\msgRepo\Request;

class ORC extends Segment implements SegmentInterface
{
    public string $name = "ORC";

    protected array $date_fields = [
        "7.0.3" => "datetime", //start date
        "9.0.0" => 'datetime',
        "15.0.0" => 'datetime',
        "27.0.0" => 'datetime',
    ];

    public function getMsg(Msg $msg): Msg
    {
        $msg->order->control = OrderControlEnum::set($this->getData(1));
        //requestnr
        $msg->order->addRequest(new Request(
            id: $this->getData(2),
        ));
        if (!$msg->order->request_nr) {
            $msg->order->request_nr = $this->getData(4);
        }
        //priority

        $msg->order->priority = match ($this->getData(7, 0, 5)) {
            "C", "S", "CITO" => true,
            "R" => false,
            default => null,
        };
        $msg->order->start_date = $this->getDate(7, 0, 3);
        //transaction datetime
        $msg->order->request_at = $this->getDate(9);

        //ordering provider
        $msg->order->requester->agbcode = $this->getData(12);
        $msg->order->requester->setName(new Name(initials: $this->getData(12, 0, 2), name: $this->getData(12, 0, 1)));
        $msg->order->requester->source = $this->getData(12, 0, 8);
        $msg->order->requester->location = $this->getData(13) ?: $this->getData(13, 0, 8);
        $msg->order->entered_by->setName(new Name(initials: $this->getData(10, 0, 2), name: $this->getData(10, 0, 1)));
        $msg->order->entered_by->agbcode = $this->getData(10);
        $msg->order->entered_by->source = $this->getData(10, 0, 8);

        if ($this->getData(21, 0, 2)) {
            $organisation = new Organisation(
                name: $this->getData(21),
                agbcode: $this->getData(21, 0, 2),
                source: $this->getData(21, 0, 5),
            );
        } elseif ($this->getData(17)) {
            $organisation = new Organisation(
                name: $this->getData(17, 0, 1),
                agbcode: $this->getData(17),
                source: $this->getData(17, 0, 2),
            );
        } elseif ($this->getData(13, 0, 3, 1)) {
            $organisation = new Organisation(
                name: $this->getData(13, 0, 3),
                agbcode: $this->getData(13, 0, 3, 1),
            );
        }
        $msg->order->organisation = $organisation ?? new Organisation();

        if (str_starts_with($this->getData(12), "0")) {
            $msg->patient->last_requester = $this->getData(12);
        } elseif (str_starts_with($this->getData(17), "0")) {
            $msg->patient->last_requester = $this->getData(17);
        } elseif (str_starts_with($this->getData(10), "0")) {
            $msg->patient->last_requester = $this->getData(10);
        } else {
            $msg->patient->last_requester = $this->getData(12);
        }
        if ($msg->order->organisation->agbcode) {
            $msg->patient->gp = $msg->order->organisation->agbcode;
        }

        $msg->sender->setAddress(new Address(
            postcode: preg_replace('/\s/', '', $this->getData(22, 0, 4)),
            city: $this->getData(22, 0, 2),
            street: $this->getData(22, 0, 0, 1),
            building: $this->getData(22, 0, 0, 2) . $this->getData(22, 0, 1),
            country: $this->getData(22, 0, 5),
        ));
        if (!$msg->sender->address->street) {
            $before = '/(?=.)\s' . $msg->sender->address->building_nr . '.*/';
            $msg->sender->address->street = preg_replace($before, "", $this->getData(22));
        }
        $msg->sender->setPhone($this->getData(23));


        return $msg;
    }

    public function setMsg($msg): self
    {
        return $this->setOrder($msg);
    }

    public function setOrder($msg): self
    {
        //order controle
        $this->setData($msg->order->control->getHl7(), 1);
        $this->setData($msg->order->requests[count($msg->order->requests)]->id ?: $msg->order->request_nr, 2);
        //requestnr
        $this->setData($msg->order->request_nr, 2);
        $this->setData($msg->order->request_nr, 4);
        //priority
        if ($msg->order->priority !== null) {
            $this->setData($msg->order->priority ? "C" : "R", 7, 0, 5);
        }
        $this->setDate($msg->order->start_date, 7, 0, 3);
        //transaction datetime
        $this->setDate($msg->order->request_at ?: Carbon::now(), 9);

        //entered by
        $this->setData($msg->order->entered_by->agbcode, 10);
        $this->setData($msg->order->entered_by->name->getLastnames(), 10, 0, 1);
        $this->setData($msg->order->entered_by->name->initials, 10, 0, 2);
        $this->setData($msg->order->entered_by->source, 10, 0, 8);

        //requester (ordering provider)
        $this->setData($msg->order->requester->agbcode, 12);
        $this->setData($msg->order->requester->name->getLastnames(), 12, 0, 1);
        $this->setData($msg->order->requester->name->initials, 12, 0, 2);
        $this->setData($msg->order->requester->source, 12, 0, 8);
        $this->setData($msg->order->requester->location, 13, 0, 8);

        //organisation
        $this->setData($msg->order->organisation->agbcode, 13, 0, 3, 1);
        $this->setData($msg->order->organisation->name, 13, 0, 3);
        $this->setData($msg->order->organisation->name, 13, 0, 8);

        //organization
        $this->setData($msg->order->organisation->agbcode, 17);
        $this->setData($msg->order->organisation->name, 17, 0, 1);
        $this->setData($msg->order->organisation->source, 17, 0, 2);

        //organization
        $this->setData($msg->order->organisation->name, 21);
        $this->setData($msg->order->organisation->agbcode, 21, 0, 2);
        $this->setData($msg->order->organisation->source, 21, 0, 5);

        //set address
        if ($msg->sender->address->street) {
            $this->setData($msg->sender->address->street . " " . $msg->sender->address->building, 22);
            $this->setData($msg->sender->address->street, 22, 0, 0, 1);
            $this->setData($msg->sender->address->building_nr, 22, 0, 0, 2);
            $this->setData($msg->sender->address->building_addition, 22, 0, 1);
            $this->setData($msg->sender->address->city, 22, 0, 2);
            $this->setData($msg->sender->address->postcode, 22, 0, 4);
            $this->setData($msg->sender->address->country ?: "NL", 22, 0, 5);
        }

        //set telephone
        if ($msg->sender->phone->number) {
            $this->setData($msg->sender->phone, 23);
            $this->setData("WPN", 23, 0, 1);
            $this->setData("PH", 23, 0, 2);
        }

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