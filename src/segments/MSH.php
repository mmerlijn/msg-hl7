<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Msg;

class MSH extends Segment implements SegmentInterface
{
    public string $name = "MSH";

    protected array $date_fields = [
        "7.0.0" => 'datetime',
    ];

    public function setMsg(Msg $msg): self
    {

        //default message splitters
        $this->data[1][0][0][0] = "DEFAULT"; // //"^~\&"; //"DEFAULT"
        //sending application
        $this->setData($msg->sender->application, 3);
        //sending facilty
        $this->setData($msg->sender->facility, 4);
        //receiving application
        $this->setData($msg->receiver->application, 5);
        //receiving application
        $this->setData($msg->receiver->facility, 6);
        //datetime of message
        $this->setDate($msg->datetime, 7);
        //security ID
        $this->setData($msg->security_id, 8);
        //msg type
        $this->setData($msg->msgType->type ?: 'ORM', 9);
        $this->setData($msg->msgType->trigger ?: '001', 9, 0, 1);
        $this->setData($msg->msgType->structure ?: 'ORM_001', 9, 0, 2);
        //controle ID'/ message ID
        $this->setData($msg->id ?: dechex(time()) . bin2hex(random_bytes(6)), 10);
        //processingId
        $this->setData("P", 11);
        //version
        if ($msg->msgType->version) {
            $this->setData($msg->msgType->version, 12);
        }
        $this->setData("NLD", 17);
        if ($msg->msgType->charset) {
            $this->setData($msg->msgType->charset, 18);
        }else{
            $this->setData("8859/1", 18);
        }
        return $this;

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

        $msg->datetime = $this->getDate(7);

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
        //charset
        $msg->msgType->charset = $this->getData(18);
        return $msg;
    }

    public function validate(): void
    {
        Validator::validate([
            "datetime_of_message" => $this->data[7][0][0][0] ?? "",
            "msg_type" => $this->data[9][0][0][0] ?? "",
            "msg_event" => $this->data[9][0][1][0] ?? "",
            "msg_structure" => $this->data[9][0][2][0] ?? "",
            "msg_controle_id" => $this->data[10][0][0][0] ?? "",
            "msg_processing_id" => $this->data[11][0][0][0] ?? "",
            "msg_version" => $this->data[12][0][0][0] ?? "",

        ], [
            "datetime_of_message" => 'required',
            "msg_type" => 'required',
            "msg_event" => 'required',
            "msg_structure" => 'required',
            "msg_controle_id" => 'required',
            "msg_processing_id" => 'required',
            "msg_version" => 'required',

        ], [
            "datetime_of_message" => '@ MSH[7][0][0][0] set/adjust $msg->datetime',
            "msg_type" => '@ MSH[9][0][0][0] set/adjust $msg->msgType->type',
            "msg_event" => '@ MSH[9][0][1][0] set/adjust $msg->msgType->event',
            "msg_structure" => '@ MSH[9][0][2][0] set/adjust $msg->msgType->structure',
            "msg_controle_id" => '@ MSH[10][0][0][0] set/adjust $msg->id',
            "msg_processing_id" => '@ MSH[11][0][0][0] set/adjust $msg->processing_id',
            "msg_version" => '@ MSH[12][0][0][0] set/adjust $msg->msgType->version',

        ]);
    }
}