<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgRepo\Msg;

class MSH extends Segment implements SegmentInterface
{
    public function setMsg(Msg $msg): void
    {

        //default message splitters
        $this->data[1][0][0][0] = "^~\&";
        //sending application
        $this->setData($msg->sender->application, 3);
        //sending facilty
        $this->setData($msg->sender->facility, 4);
        //receiving application
        $this->setData($msg->receiver->application, 5);
        //receiving application
        $this->setData($msg->receiver->facility, 6);
        //datetime of message
        $this->setData($msg->datetime->format("YmdHisO"), 7);
        //security ID
        $this->setData($msg->security_id, 8);
        //msg type
        $this->setData($msg->msgType->type ?: 'ORM', 9);
        $this->setData($msg->msgType->trigger ?: '001', 9, 0, 1);
        $this->setData($msg->msgType->structure ?: 'ORM_001', 9, 0, 2);
        //controle ID'/ message ID
        $this->setData($msg->id, 10);
        //processingId
        $this->setData("P", 11);
        //version
        //$this->setData($msg->msgType->version ?? "2.4", 12);

    }


    public function getMsg(Msg $msg): Msg
    {
        //sending application
        $msg->sender->application = $this->getData(3);
        //sending facilty
        $msg->sender->facility = $this->getData(4);
        //receiving application
        $msg->receiver->application = $this->getData(5);
        //receiving facility
        $msg->receiver->facility = $this->getData(6);
        //datetime of message
        $dt = $this->getData(7);
        if (strlen($dt) > 14) {
            $msg->datetime = Carbon::createFromFormat("YmdHisO", $dt);
        } else {
            $msg->datetime = Carbon::createFromFormat("YmdHis", $dt);
        }
        //security ID
        $msg->security_id = $this->getData(8);
        //msg type
        $msg->msgType->type = $this->getData(9);
        $msg->msgType->trigger = $this->getData(9, 0, 1);
        $msg->msgType->structure = $this->getData(9, 0, 2);
        //control ID
        $msg->id = $this->getData(10);
        //processing ID
        $msg->processing_id = $this->getData(11);
        //version
        $msg->msgType->version = $this->getData(12);

        return $msg;
    }
}