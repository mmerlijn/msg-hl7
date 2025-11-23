<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Enums\ResultFlagEnum;
use mmerlijn\msgRepo\Enums\ValueTypeEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Observation;
use mmerlijn\msgRepo\Result;
use mmerlijn\msgRepo\TestCode;

class OBX extends Segment implements SegmentInterface
{
    public string $name = "OBX";

    protected array $date_fields = [
        "14.0.0" => 'datetime',
        "19.0.0" => 'datetime',
    ];

    public function getMsg(Msg $msg): Msg
    {
        if (empty($msg->order->requests)) {
            $msg->order->addRequest();
        }
        $observation = new Observation(
            type: $this->getData(2),
            value: $this->getData(5),
            test: new TestCode(
                code: $this->getData(3),
                value: $this->getData(3, 0, 1),
                source: $this->getData(3, 0, 2),
            ),

            units: $this->getData(6),
            reference_range: $this->getData(7),
            abnormal_flag: ResultFlagEnum::set($this->getData(8)),
            done: in_array($this->getData(11), ["F", "C"]),
            change: $this->getData(11) == "C",
        );
        if ($observation->type == ValueTypeEnum::CE) {
            $i = 0;
            while ($this->getData(5, $i, 1)) {
                $observation->addValue(new TestCode(
                    code: $this->getData(5, $i, 0),
                    value: $this->getData(5, $i, 1),
                    source: $this->getData(5, $i, 2),
                ));
                $i++;
            }
        }


        $msg->order->requests[count($msg->order->requests) - 1]->addObservation($observation);
        //dt of observation
        if (!$msg->order->observation_at)
            $msg->order->observation_at = $this->getDate(14);
        //dt of analysis
        if (!$msg->order->analysis_at)
            $msg->order->analysis_at = $this->getDate(19);
        return $msg;
    }

    // for testing purposes only
    public function setMsg(Msg $msg): self
    {
        return $this->setObservation($msg, 0, 0);
    }

    public function setObservation(Msg $msg, $request_key, $result_key): self
    {
        //count id
        $this->setData($result_key + 1, 1);
        //type of result
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->type->value, 2);
        //test code / name
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->test->code, 3);
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->test->value, 3, 0, 1);
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->test->source ?: $msg->default_source, 3, 0, 2);
        //result
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->value, 5);
        foreach ($msg->order->requests[$request_key]->observations[$result_key]->values as $i => $testCode) {
            $this->setData($testCode->code, 5, $i, 0);
            $this->setData($testCode->value, 5, $i, 1);
            $this->setData($testCode->source, 5, $i, 2);
        }
        //units
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->units, 6);
        //reference range
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->reference_range, 7);
        //abnormal flag
        $this->setData($msg->order->requests[$request_key]->observations[$result_key]->abnormal_flag->value, 8);

        //result status
        if ($msg->order->requests[$request_key]->observations[$result_key]->change) {
            $this->setData("C", 11); //correction/change
        } elseif ($msg->order->requests[$request_key]->observations[$result_key]->done) {
            $this->setData("F", 11); //final
        } else {
            $this->setData("P", 11); //Preliminary results
        }
        //dt of observation
        if ($msg->order->observation_at)
            $this->setDate($msg->order->observation_at, 14);
        //dt of analysis
        if ($msg->order->analysis_at)
            $this->setDate($msg->order->analysis_at, 19);
        if ($msg->order->requests[$request_key]->observations[$result_key]->hasValues()) {
            $i = 0;
            foreach ($msg->order->requests[$request_key]->observations[$result_key]->values as $v) {
                $this->setData($v->code, 5, $i, 0);
                $this->setData($v->value, 5, $i, 1);
                $this->setData($v->source, 5, $i, 2);
                $i++;
            }
        }
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "result_id" => $this->data[1][0][0][0] ?? "",
            "result_type" => $this->data[2][0][0][0] ?? "",
            "result_identifier" => $this->data[3][0][0][0] ?? "",
            "result_value" => $this->data[5][0][0][0] ?? "",
            "result_status" => $this->data[11][0][0][0] ?? "",
        ], [
            "result_id" => 'required|numeric',
            "result_type" => 'required',
            "result_identifier" => 'required',
            "result_value" => 'required',
            "result_status" => 'required',
        ], [
            "result_id" => '@ OBX[1][0][0][0] debug OBX',
            "result_type" => '@ OBX[2][0][0][0] debug OBX',
            "result_identifier" => '@ OBX[3][0][0][0] set/adjust $msg->order->observations[..]->test_code',
            "result_value" => '@ OBX[5][0][0][0] set/adjust $msg->order->observations[..]->value',
            "result_status" => '@ OBX[11][0][0][0] set/adjust $msg->order->observations[..]->done / change',
        ]);
    }

    private function makeTestcode(): string
    {
        if (!$this->getData(3)) {
            if (strlen($this->getData(3, 0, 1)) > 20) {
                $tmp = explode(" ", $this->getData(3, 0, 1));
                $tmp = array_map(fn($v) => substr($v, 0, 1), $tmp);
                return substr(strtoupper(implode("", $tmp)), 0, 20);
            } else {
                return substr(str_replace(" ", "_", strtoupper($this->getData(3, 0, 1))), 0, 20);
            }
        }
        return $this->getData(3);
    }

}