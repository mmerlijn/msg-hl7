<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Specimen;
use mmerlijn\msgRepo\TestCode;

class SPM extends Segment implements SegmentInterface
{
    public string $name = "SPM";

    public function getMsg(Msg $msg): Msg
    {
        $specimen = new Specimen(
            id: $this->getData(2),
            test: new TestCode(
                code: $this->getData(4),
                value: $this->getData(4, 0, 1),
                source: $this->getData(4, 0, 2),
            ),
            container: new TestCode(
                code: $this->getData(27),
                value: $this->getData(27, 0, 1),
                source: $this->getData(27, 0, 2),
            )

        );
        $req_index = count($msg->order->requests) - 1;
        if ($req_index < 0) {
            $msg->order->addRequest();
            $req_index = 0;
        }
        $msg->order->requests[$req_index]->addSpecimen($specimen);
        return $msg;
    }

    public function setMsg(Msg $msg): self
    {
        return $this->setSpecimen($msg);
    }

    public function setSpecimen(Msg $msg, int $request_key = 0, $specimen_key = 0): self
    {

        $this->setData("1", $specimen_key + 1); //specimen nr
        if ($msg->order->requests[$request_key]?->specimens[$specimen_key]?->test->value) {
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->test->code, 4);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->test->value, 4, 0, 1);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->test->source ?: $msg->default_source, 4, 0, 2);
        }
        $this->setDate($msg->order->request_at, 7);
        $this->setData("N", 20);
        //container type 27
        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->container->code, 27);
        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->container->value, 27, 0, 1);
        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->container->source ?: $msg->default_source, 27, 0, 2);
        $this->msgSegmentSetter($msg, $request_key);
        return $this;
    }
}