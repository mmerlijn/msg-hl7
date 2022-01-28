<?php

namespace mmerlijn\msgHl7;

use mmerlijn\msgHl7\helpers\Encoding;
use mmerlijn\msgHl7\segments\IN1;
use mmerlijn\msgHl7\segments\MSH;
use mmerlijn\msgHl7\segments\PID;
use mmerlijn\msgHl7\segments\PV1;
use mmerlijn\msgHl7\segments\PV2;
use mmerlijn\msgHl7\segments\Undefined;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Msg;

class Hl7
{
    private string $msg = "";
    public string $type = "ORM";
    public array $segments = [];

    public function __construct(string $hl7 = "")
    {
        if ($hl7) {
            $this->msg = $hl7;
            $this->buildSegments();
        }
        return $this;
    }

    public function read(string $hl7): self
    {
        $this->msg = $hl7;
        $this->buildSegments();
        return $this;
    }

    public function write(bool $validate = false): string
    {
        Validator::reset();
        $output = "";
        foreach ($this->segments as $teller => $segment) {
            if ($validate)
                $segment->validate();
            if ($segment->name == "MSH")
                unset($segment->data[2]);
            $output .= $segment->write() . chr(13);
        }
        if (Validator::fails()) {
            throw new \Exception("Edifact validation fails: " . PHP_EOL . implode(PHP_EOL, Validator::getErrors()));
        }
        return $output;
    }

    public function getMsg(Msg $msg): Msg
    {
        foreach ($this->segments as $segment) {
            $msg = $segment->getMsg($msg);
        }
        return $msg;
    }

    public function setMsg(Msg $msg): self
    {
        $this->type = $msg->msgType->type ?: "ORM";

        if (empty($this->segments)) {
            $this->createDefaultSegments();
        }
        foreach ($this->segments as $k => $segment) {
            $this->segments[$k]->setMsg($msg);
        }
        //set results
        //if (!empty($msg->order->results)) {
        //    $teller_BEP = 1;
        //    $teller_NUB = 1;
        //    foreach ($msg->order->results as $k => $result) {
        //        if ($result->done) {
        //            array_splice($this->segments, $this->findSegmentKey("IDE") + 1, 0, [(new BEP("BEP:1:1:$teller_BEP"))->setResult($result)]);
        //            $teller_OPB = 1;
        //            foreach ($result->comments as $comment) {
        //                array_splice($this->segments, $this->findSegmentKey("BEP") + 1, 0, [(new OPB("OPB:1:1:$teller_BEP:$teller_OPB"))->setComment($comment)]);
        //                $teller_OPB++;
        //            }
        //            $teller_BEP++;
        //        } else {
        //            array_splice($this->segments, $this->findSegmentKey("UNT"), 0, [(new NUB("NUB:1:$teller_NUB+"))->setResult($result)]);
        //            $teller_NUB++;
        //        }
        //    }
        //}
        //zet comments
        //if (!empty($msg->comments)) {
        //    $teller_TXT = 1;
        //    foreach ($msg->comments as $comment) {
        //        array_splice($this->segments, $this->findSegmentKey("GGO"), 0, [(new TXT("TXT:$teller_TXT"))->setComment($comment)]);
        //        $teller_TXT++;
        //    }
        //}
        return $this;
    }

    //search for first segment occurrence
    public function findSegmentKey(string $SEG)
    {
        foreach ($this->segments as $k => $segment) {
            if ($segment->name == $SEG) {
                return $k;
            }
        }
        return count($this->segments);
    }

    protected function buildSegments(): void
    {
        if (strlen($this->msg)) {

            // The first segment should be the control segment
            if (!preg_match('/^(MSH)+(.)(.)(.)(.)(.)(.)/', $this->msg, $matches)) {
                throw new \Exception('MSH header is not valid expect MSH|^~\&| or something like that got ' . substr($this->msg, 0, 9));
            }
            //set the encoding
            Encoding::setSeparator($matches);
            $this->msg = "MSH|DEFAULT|" . substr($this->msg, 8);

            $this->segments = [];
            $lines = preg_split("/\r\n|\n|\r/", trim($this->msg));
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line)) {
                    $segment = 'mmerlijn\\msgHl7\\segments\\' . substr($line, 0, 3);
                    if (class_exists($segment)) {
                        $this->segments[] = new $segment($line);
                    } else {
                        $this->segments[] = new Undefined($line);
                    }
                }
            }
        }
    }

    //MEDLAB
    protected function createDefaultSegments()
    {
        if ($this->type == "ORM") {
            $this->segments = [
                new MSH("MSH|DEFAULT||||||||ORM^O01^ORM_O01||P|2.4|||||NLD|8859/1"),
                new PID("PID|1||||^^^^^^L||||||&&^^^^^NL^M||||||||||||||||||||Y|NNNLD"),
                new PV1("PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V"),
                new PV2("PV2|||"),
                new IN1("IN1|1|^null||||||||||||||||||||||||||||||||||")

            ];
        } elseif ($this->type == "") {
            $this->segments = [
            ];
        }
    }
}