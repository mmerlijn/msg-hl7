<?php

namespace mmerlijn\msgHl7;

use mmerlijn\msgHl7\helpers\Encoding;
use mmerlijn\msgHl7\segments\IN1;
use mmerlijn\msgHl7\segments\MSH;
use mmerlijn\msgHl7\segments\NTE;
use mmerlijn\msgHl7\segments\OBR;
use mmerlijn\msgHl7\segments\OBX;
use mmerlijn\msgHl7\segments\ORC;
use mmerlijn\msgHl7\segments\PID;
use mmerlijn\msgHl7\segments\PV1;
use mmerlijn\msgHl7\segments\PV2;
use mmerlijn\msgHl7\segments\Segment;
use mmerlijn\msgHl7\segments\SegmentInterface;
use mmerlijn\msgHl7\segments\Undefined;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Msg;

class Hl7
{
    private string $msg = "";
    public string $type = "ORM";
    public array $segments = [];
    public bool $repeat_ORC = false;
    public string $datetime_format = "YmdHisO";

    public function __construct(string $hl7 = "")
    {
        if ($hl7) {
            $this->msg = $hl7;
            $this->buildSegments();
        }
        return $this;
    }

    public function setRepeatORC(bool $bool = true)
    {
        $this->repeat_ORC = $bool;
        return $this;
    }

    public function setDatetimeFormat(string $format): self
    {
        $this->datetime_format = $format;
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
            $segment->setDatetimeFormat($this->datetime_format);
            if ($validate)
                $segment->validate();
            if ($segment->name == "MSH") {
                unset($segment->data[2]);
                $output .= str_replace("DEFAULT", "^~\&", $segment->write()) . chr(13); //"^~\&"
            } else {
                $line = $segment->write();
                if (strlen($line) > 3) {
                    $output .= $line . chr(13);
                }
            }


        }
        if (Validator::fails()) {
            throw new \Exception("HL7 validation fails: " . PHP_EOL . implode(PHP_EOL, Validator::getErrors()));
        }
        return $output;
    }

    public function getMsg(?Msg $msg = null): Msg
    {
        if (!$msg) {
            $msg = new Msg();
        }
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
        //for storing present data
        $orc_line = "";
        $obr_line = [];
        //remove order segments and set msg
        foreach ($this->segments as $k => $segment) {
            if (in_array($segment->name, ["ORC", "OBR", "OBX", "NTE"])) {
                if ($segment->name == "ORC") { //store present data ORC
                    $orc_line = $segment->line;
                }
                if ($segment->name == "OBR") { //store present data OBR
                    $obr_line[$segment->getData(4)] = $segment->line;
                }
                unset($this->segments[$k]);
            } else {
                $this->segments[$k]->setDatetimeFormat($this->datetime_format)->setMsg($msg);
            }
        }
        $this->segments = array_values($this->segments);
        if (!empty($msg->order->requests)) {
            $orc_done = false;
            foreach ($msg->order->requests as $k => $request) {
                if ($this->repeat_ORC or $orc_done == false) {
                    $this->segments[] = (new ORC($orc_line))->setDatetimeFormat($this->datetime_format)->setOrder($msg);
                    $orc_done = true;
                }
                $this->segments[] = (new OBR($obr_line[$request->test_code] ?? ""))->setDatetimeFormat($this->datetime_format)->setRequest($msg, $k);
                foreach ($msg->order->results as $k2 => $result) {
                    $this->segments[] = (new OBX())->setDatetimeFormat($this->datetime_format)->setResults($msg, $k2);
                    if (!empty($result->comments)) {
                        foreach ($result->comments as $id => $comment) {
                            $this->segments[] = (new NTE())->setComment($id, $comment);
                        }
                    }
                }
                if (!empty($request->comments)) {
                    foreach ($request->comments as $id => $comment) {
                        $this->segments[] = (new NTE())->setComment($id, $comment);
                    }
                }
            }
        }
        if (!empty($msg->comments)) {
            foreach ($msg->comments as $id => $comment) {
                $this->segments[] = (new NTE())->setComment($id, $comment);
            }
        }
        return $this;
    }

    //search for first segment occurrence
    public function findSegmentKey(string $SEG, $number = 0): int
    {
        $i = 0;
        foreach ($this->segments as $k => $segment) {
            if ($segment->name == $SEG) {
                if ($i == $number) {
                    return $k;
                }
                $i++;
            }
        }
        return count($this->segments);
    }

    public function addSegment(SegmentInterface $segment): self
    {
        $this->segments[] = $segment;
        return $this;
    }

    public function removeSegment(string $SEG, $number = 0): self
    {
        $key = $this->findSegmentKey($SEG, $number);
        if ($key != count($this->segments)) {
            unset($this->segments[$key]);
            $this->segments = array_values($this->segments);
        }
        return $this;
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
                (new MSH("MSH|DEFAULT||||||||ORM^O01^ORM_O01||P|2.4|||||NLD|8859/1"))->setDatetimeFormat($this->datetime_format),
                (new PID("PID|1||||^^^^^^L||||||&&^^^^^NL^M||||||||||||||||||||Y|NNNLD"))->setDatetimeFormat($this->datetime_format),
                (new PV1("PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V"))->setDatetimeFormat($this->datetime_format),
                (new PV2("PV2|||"))->setDatetimeFormat($this->datetime_format),
                (new IN1("IN1|1|^null||||||||||||||||||||||||||||||||||"))->setDatetimeFormat($this->datetime_format),
            ];
        } elseif ($this->type == "OML") {
            $this->segments = [
                (new MSH("MSH|DEFAULT||||||||OML^021^OML_O21||P|2.5|||||NLD|8859/1"))->setDatetimeFormat($this->datetime_format),
                (new PID("PID|1||||^^^^^^L||||||&&^^^^^NL^M||||||||||||||||||||Y|NNNLD"))->setDatetimeFormat($this->datetime_format),
            ];
        } elseif ($this->type == "") {
            $this->segments = [
            ];
        }
    }
}