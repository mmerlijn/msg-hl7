<?php

namespace mmerlijn\msgHl7\segments;

use Carbon\Carbon;
use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Enums\ResultFlagEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Option;
use mmerlijn\msgRepo\Result;

class OBX extends Segment implements SegmentInterface
{
    public string $name = "OBX";

    protected array $date_fields = [
        "14.0.0" => 'datetime',
        "19.0.0" => 'datetime',
    ];

    public function getMsg(Msg $msg): Msg
    {
        $result = new Result(
            value: $this->getData(5),
            test_code: $this->makeTestcode(),
            test_name: $this->getData(3, 0, 1),
            test_source: $this->getData(3, 0, 2),
            other_test_name: $this->getData(5, 0, 1),
            other_test_source: $this->getData(5, 0, 2),
            units: $this->getData(6),
            reference_range: $this->getData(7),
            abnormal_flag: ResultFlagEnum::set($this->getData(8)),
            done: in_array($this->getData(11), ["F", "C"]) ? true : false,
            change: ($this->getData(11) == "C") ? true : false,
        );
        if ($this->getData(2) == "CE") {
            $i = 0;
            while ($this->getData(5, $i, 1)) {
                $result->addOption(new Option(
                    label: $this->getData(5, $i, 0),
                    value: $this->getData(5, $i, 1),
                    source: $this->getData(5, $i, 2),
                ));
                $i++;
            }
        }


        $msg->order->addResult($result
        );
        //dt of observation
        if (!$msg->order->dt_of_observation)
            $msg->order->dt_of_observation = $this->getDate(14);
        //dt of analysis
        if (!$msg->order->dt_of_analysis)
            $msg->order->dt_of_analysis = $this->getDate(19);
        return $msg;
    }

    public function setResults(Result $result, Msg $msg, $result_key): self
    {
        //count id
        $this->setData($result_key + 1, 1);
        //type of result
        if ($result->type_of_value) {
            $this->setData($result->type_of_value, 2);
        } elseif ($result->other_test_name or !empty($result->options)) {
            $this->setData("CE", 2);
        } elseif (is_numeric($result->value)) {
            $this->setData("NM", 2);
        } else {
            $this->setData("ST", 2);
        }
        //test code / name
        $this->setData($result->test_code, 3);
        $this->setData($result->test_name, 3, 0, 1);
        $this->setData($result->test_source ?: '99zdl', 3, 0, 2);
        //result
        $this->setData($result->value, 5);
        $this->setData($result->other_test_name, 5, 0, 1);
        $this->setData($result->other_test_source, 5, 0, 2);
        //units
        $this->setData($result->units, 6);
        //reference range
        $this->setData($result->reference_range, 7);
        //abnormal flag
        $this->setData($result->abnormal_flag->value, 8);
        //result status

        if ($result->change) {
            $this->setData("C", 11); //correction/change
        } elseif ($result->done) {
            $this->setData("F", 11); //final
        } else {
            $this->setData("P", 11); //Preliminary results
        }
        //dt of observation
        if ($msg->order->dt_of_observation)
            $this->setDate($msg->order->dt_of_observation, 14);
        //dt of analysis
        if ($msg->order->dt_of_analysis)
            $this->setDate($msg->order->dt_of_analysis, 19);
        if (!empty($result->options)) {
            $i = 0;
            foreach ($result->options as $option) {
                $this->setData($option->label, 5, $i, 0);
                $this->setData($option->value, 5, $i, 1);
                $this->setData($option->source, 5, $i, 2);
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
            "result_identifier" => '@ OBX[3][0][0][0] set/adjust $msg->order->results[..]->test_code',
            "result_value" => '@ OBX[5][0][0][0] set/adjust $msg->order->results[..]->value',
            "result_status" => '@ OBX[11][0][0][0] set/adjust $msg->order->results[..]->done / change',
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