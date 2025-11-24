<?php

namespace mmerlijn\msgHl7;

use mmerlijn\msgHl7\helpers\Encoding;
use mmerlijn\msgHl7\segments\BLG;
use mmerlijn\msgHl7\segments\IN1;
use mmerlijn\msgHl7\segments\MSH;
use mmerlijn\msgHl7\segments\NTE;
use mmerlijn\msgHl7\segments\OBR;
use mmerlijn\msgHl7\segments\OBX;
use mmerlijn\msgHl7\segments\ORC;
use mmerlijn\msgHl7\segments\PID;
use mmerlijn\msgHl7\segments\PV1;
use mmerlijn\msgHl7\segments\PV2;
use mmerlijn\msgHl7\segments\SegmentInterface;
use mmerlijn\msgHl7\segments\SPM;
use mmerlijn\msgHl7\segments\TQ1;
use mmerlijn\msgHl7\segments\Undefined;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Msg;

class Hl7
{
    private array $msgSegmentsToGet = [];
    private string $msg = "";
    public array $segments = [];
    public bool $repeat_ORC = true;
    public bool $use_tq1 = false;

    protected array $useSegments = [
        "MSH",
        "PID",
        "PV1",
        "PV2",
        "IN1",
        "ORC",
        "OBR",
        "OBX",
        "NTE",
        "TQ1",
        "SPM",
    ];
    public string $datetime_format = "YmdHisO";

    public function __construct(string $hl7 = "")
    {
        if ($hl7) {
            $this->setHl7($hl7);
            $this->buildSegments();
        }
        return $this;
    }

    public function setRepeatORC(bool $bool = true)
    {
        $this->repeat_ORC = $bool;
        return $this;
    }

    public function getSegment(string $identifier): self
    {
        $this->msgSegmentsToGet[] = $identifier;
        return $this;
    }

    public function useTQ1(): self
    {
        if (!in_array("TQ1", $this->useSegments)) {
            $this->useSegments[] = "TQ1";
        }
        return $this;
    }

    public function setUseSegments(array $segments, bool $add = false): self
    {
        if (!$add) {
            $this->useSegments = $segments;
            return $this;
        }
        $this->useSegments = array_merge($segments, $this->useSegments);
        return $this;
    }

    public function setDatetimeFormat(string $format): self
    {
        $this->datetime_format = $format;
        return $this;
    }

    public function read(string $hl7): self
    {
        $this->setHl7($hl7);
        $this->buildSegments();
        return $this;
    }

    public function write(bool $validate = false): string
    {
        Validator::reset();
        $output = "";
        $orc_counter = 0;
        foreach ($this->segments as $teller => $segment) {
            $segment->setDatetimeFormat($this->datetime_format);
            if ($validate)
                $segment->validate();
            if ($segment->name == "MSH") {
                unset($segment->data[2]);
                $output .= str_replace("DEFAULT", "^~\&", $segment->write()) . chr(13); //"^~\&"
            } elseif (!$this->repeat_ORC and $orc_counter > 0 and $segment->name == "ORC") {
                //skip
            } else {
                if ($segment->name == "ORC") {
                    $orc_counter++;
                }
                if (in_array($segment->name, $this->useSegments)) {

                    $line = $segment->write();
                    if (strlen($line) > 3) {
                        $output .= $line . chr(13);
                    }
                }
            }
        }
        if (Validator::fails()) {
            throw new \Exception("HL7 validation fails: " . PHP_EOL . implode(PHP_EOL, Validator::getErrors()));
        }
        return $output;
    }

    public function filterRequestCode(string|array $test_code): self
    {
        if (is_string($test_code)) {
            $test_code = [$test_code];
        }
        //TODO segment teller aanpassen bij verwijderen
        $this->segments = array_filter($this->segments, function ($segment) use ($test_code) {
            if ($segment->name == "OBR") {
                return !in_array($segment->getData(4), $test_code);
            }

            return true;
        });
        return $this;
    }

    public function getMsg(?Msg $msg = null): Msg
    {
        if (!$msg) {
            $msg = new Msg();
        }
        foreach ($this->msgSegmentsToGet as $v) {
            $msg->addSegment($v);
        }
        $previous_segment = null;
        foreach ($this->segments as $segment) {
            $msg = $segment->getMsg($msg);
            if ($segment->name == "NTE") {
                $msg = $segment->getComment($msg, $previous_segment);
            }
            if (in_array($segment->name, ["MSH", "PID", "OBR", "OBX"])) {
                $previous_segment = $segment->name;
            }
        }
        return $msg;
    }

    public function setMsg(Msg $msg): self
    {
        if (!$this->hasSegment("MSH")) {
            $this->segments = [match ($msg->msgType->structure) {
                "OML_O21" => (new MSH("MSH|DEFAULT||||||||OML^O21^OML_O21||P|2.5.1|||||NLD|8859/1"))->setMsg($msg),
                "ORM_O01" => (new MSH("MSH|DEFAULT||||||||ORM^O01^ORM_O01||P|2.4|||||NLD|8859/1"))->setMsg($msg),
                default => (new MSH("MSH|DEFAULT||||||||||||||||NLD|8859/1"))->setMsg($msg),
            }, ...$this->segments];
        } else {
            $this->segments[$this->findSegmentKey("MSH")]->setMsg($msg);
        }
        if ($msg->msgType->structure == "OML_O21" and $msg->hasComments() and in_array("NTE", $this->useSegments)) {
            if ($this->segments[1]?->name != "NTE") {
                //make space for NTE after MSH
                array_splice($this->segments, 1, 0, [(new NTE())->setComment(0, $msg->comments[0])]);
            } else {
                foreach ($msg->comments as $id => $comment) {
                    $this->segments[$id + 1]->setComment($id, $comment);
                }
            }
        }
        //set patient segments
        if (!$this->hasSegment("PID")) {
            $this->segments[] = (new PID("PID|1||||^^^^^^L||||||&&^^^^^NL^M||||||||||||||||||||Y|NNNLD"))->setMsg($msg);
            if ($msg->patient->hasComments() and in_array("NTE", $this->useSegments)) {
                foreach ($msg->patient->comments as $id => $comment) {
                    $this->segments[] = (new NTE)->setComment($id, $comment);
                }
            }
        } else {
            $pid_key = $this->findSegmentKey("PID");
            $this->segments[$pid_key]->setMsg($msg);
            //insert NTE comments after PID
            if ($msg->patient->hasComments() and in_array("NTE", $this->useSegments)) {
                $k = $pid_key + 1;
                foreach ($msg->patient->comments as $id => $comment) {
                    if ($this->segments[$k]?->name != "NTE") {
                        //make space for NTE after PID
                        array_splice($this->segments, $k, 0, [(new NTE)->setComment($id, $comment)]);
                    } else {
                        $this->segments[$k]->setComment($id, $comment);
                    }
                    $k++;
                }
            }
        }
        if (!$this->hasSegment("PV1")) {
            $this->segments[] = (new PV1("PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V"))->setMsg($msg);
        } else {
            $this->segments[$this->findSegmentKey("PV1")]->setMsg($msg);
        }
        if (!$this->hasSegment("PV2")) {
            $this->segments[] = (new PV2("PV2|||"))->setMsg($msg);
        } else {
            $this->segments[$this->findSegmentKey("PV2")]->setMsg($msg);
        }
        if (!$this->hasSegment("IN1")) {
            $this->segments[] = (new IN1("IN1|1|^null||||||||||||||||||||||||||||||||||"))->setMsg($msg);
        } else {
            $this->segments[$this->findSegmentKey("IN1")]->setMsg($msg);
        }
        //reset all other segments
        $k = $this->findSegmentKey("IN1");
        $this->segments = array_slice($this->segments, 0, $k + 1);

        //set order segments
        foreach ($msg->order->requests as $req_k => $request) {
            if ($this->repeat_ORC or $req_k == 0) {
                $this->segments[] = (new ORC)->setOrder($msg, $req_k);
            }
            if (in_array("TQ1", $this->useSegments)) {
                $this->segments[] = (new TQ1)->setRequest($msg, $req_k);
            }
            $this->segments[] = (new OBR)->setRequest($msg, $req_k);
            if ($request->hasComments()) {
                foreach ($request->comments as $id => $comment) {
                    $this->segments[] = (new NTE())->setComment($id, $comment);
                }
            }
            foreach ($request?->observations ?? [] as $obs_k => $observation) {
                $this->segments[] = (new OBX())->setObservation($msg, $req_k, $obs_k);
                if ($observation->hasComments()) {
                    foreach ($observation->comments as $id => $comment) {
                        $this->segments[] = (new NTE())->setComment($id, $comment);
                    }
                }
            }
            if (in_array("SPM", $this->useSegments) && $request->hasSpecimens()) {
                foreach ($request->specimens as $sp_k => $specimen) {
                    $this->segments[] = (new SPM())->setSpecimen($msg, $req_k, $sp_k);
                }
            }
        }
        if (in_array("BLG", $this->useSegments)) {
            $this->segments[] = (new BLG("BLG||CH"))->setMsg($msg);
        }
        return $this;
    }

    private function hasSegment(string $SEG): bool
    {
        foreach ($this->segments as $segment) {
            if ($segment->name == $SEG) {
                return true;
            }
        }
        return false;
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


    private function setHl7(string $hl7): void
    {
        $this->msg = preg_replace('/^(MSH\|\^.+)(MSH\|\^.+)/s', '$1', $hl7);
    }
}