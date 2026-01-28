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
            type: new TestCode(
                code: $this->getData(4),
                value: $this->getData(4, 0, 1),
                source: $this->getData(4, 0, 2),
            ),
            available: $this->getData(20) !== 'N',
            container: new TestCode(
                code: $this->getData(27),
                value: $this->getData(27, 0, 1),
                source: $this->getData(27, 0, 2),
            ),
            collection_method: $this->getData(7),
            collection_source: $this->getData(8),
            collection_source_modifier: $this->getData(9),
        );
        if( $this->getDate(17)){
            $msg->order->observation_at = $this->getDate(17);
        }
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
        if ($msg->order->requests[$request_key]?->specimens[$specimen_key]?->type->value) {
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->type->code, 4);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->type->value, 4, 0, 1);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->type->source ?: $msg->default_source, 4, 0, 2);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->id, 2);
        }
        if($msg->order->requests[$request_key]->specimens[$specimen_key]->collection_method){
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->collection_method, 7);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->collection_source, 8);
            $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->collection_source_modifier, 9);
        }
        if($msg->order->observation_at){
            $this->setDate($msg->order->observation_at, 17);
        }
        if($msg->order->observation_at){
            $this->setDate($msg->order->observation_at, 17);
        }

        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->available?"Y":"N", 20);
        //container type 27
        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->container->code, 27);
        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->container->value, 27, 0, 1);
        $this->setData($msg->order->requests[$request_key]->specimens[$specimen_key]->container->source ?: $msg->default_source, 27, 0, 2);
        $this->msgSegmentSetter($msg, $request_key);
        return $this;
    }
}