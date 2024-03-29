<?php

namespace mmerlijn\msgHl7\segments;


use Carbon\Carbon;
use mmerlijn\msgRepo\Msg;

class Segment implements SegmentInterface
{
    public $repeat = false; //default segment is not repeatable
    protected array $date_fields = [];
    public string $name;    //name of the segment
    public array $data;     //data of the segment (multi dimensional)
    public string $datetime_format = "YmdHisO";

    public function __construct(public string $line = "")
    {
        $this->resetData();
        if ($line) {
            $this->setName();
            $this->lineToFields();
        } else {
            $this->setData($this->name ?? "", 0); //set segment name
        }
        return $this;
    }

    public function read(string $line): self
    {
        $this->line = $line;
        $this->setName();
        $this->lineToFields();

        return $this;
    }

    //public function write(): string
    //{
    //    $fields = [];
    //    foreach ($this->data as $f => $i) {
    //        if (in_array($f, array_keys($this->date_fields)) and $i[0][0][0] ?? "" instanceof Carbon) {
    //            $fields[] = $i[0][0][0]->format($this->date_fields[$f] == 'datetime' ? $this->datetime_format : 'Ymd');
    //        } else {
    //            $rep = [];
    //            foreach ($i as $j) {
    //                $components = [];
    //                foreach ($j as $k) {
    //                    $components[] = preg_replace("/\.*(&*)$/", "", implode("&", $k));
    //                }
    //                $rep[] = preg_replace("/\.*(\^*)$/", "", implode("^", $components));
    //            }
    //            $fields[] = preg_replace("/\.*(~*)$/", "", implode("~", $rep));
    //        }
    //    }
    //    return preg_replace("/\.*(\|*)$/", "", implode("|", $fields));
    //}
    public function write(): string
    {
        $fields = [];
        foreach ($this->data as $f => $i) {
            //if (in_array($f, array_keys($this->date_fields)) and $i[0][0][0] ?? "" instanceof Carbon) {
            //    $fields[] = $i[0][0][0]->format($this->date_fields[$f] == 'datetime' ? $this->datetime_format : 'Ymd');
            //} else {
            $rep = [];
            foreach ($i as $r => $j) {
                $components = [];
                foreach ($j as $c => $k) {

                    if (in_array("$f.$r.$c", array_keys($this->date_fields)) and ($k[0] ?? false) instanceof Carbon) { //
                        $components[] = $k[0]->format(($this->date_fields["$f.$r.$c"] == 'datetime') ? $this->datetime_format : 'Ymd');
                    } else {
                        $components[] = preg_replace("/\.*(&*)$/", "", implode("&", $k));
                    }
                }
                $rep[] = preg_replace("/\.*(\^*)$/", "", implode("^", $components));
            }
            $fields[] = preg_replace("/\.*(~*)$/", "", implode("~", $rep));
            //}
        }
        return preg_replace("/\.*(\|*)$/", "", implode("|", $fields));
    }

    public function getMsg(Msg $msg): Msg
    {
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {

    }

    public function validate(): void
    {
    }

    public function setDatetimeFormat(string $format): self
    {
        $this->datetime_format = $format;
        return $this;
    }

    public function setData(mixed $value, int $field, int $repetition = 0, int $component = 0, int $subComponent = 0): self
    {
        if (!($this->data[$field][$repetition][$component][$subComponent] ?? false)) {
            $this->expandData($field, $repetition, $component, $subComponent);
        }
        $this->data[$field][$repetition][$component][$subComponent] = preg_replace('/(\||~|\^|\&)/', '\$1', $value ?? "");
        return $this;
    }

    public function setDate(Carbon|string|null $value, int $field, int $repetition = 0, int $component = 0, int $subComponent = 0): self
    {
        if (!($this->data[$field][$repetition][$component][$subComponent] ?? false)) {
            $this->expandData($field, $repetition, $component, $subComponent);
        }
        if ($value instanceof Carbon) {
            $this->data[$field][$repetition][$component][$subComponent] = $value;
        } elseif (gettype($value) == "string") {
            $this->data[$field][$repetition][$component][$subComponent] = Carbon::create($value);
        } else {
            $this->data[$field][$repetition][$component][$subComponent] = null;
        }
        return $this;
    }


    public function getData(int $field, int $repetition = 0, int $component = 0, int $subComponent = 0): string
    {
        return preg_replace('/\\\(\||~|\^|&)/', '$1', $this->data[$field][$repetition][$component][$subComponent] ?? "");
    }

    public function getDate(int $field, int $repetition = 0, int $component = 0, int $subComponent = 0): ?Carbon
    {
        if (($this->data[$field][$repetition][$component][$subComponent] ?? "") instanceof Carbon)
            return $this->data[$field][$repetition][$component][$subComponent] ?? null;
        $date = $this->getData($field, $repetition, $component, $subComponent);
        return $this->toCarbon($date);
    }

    public function toCarbon(string $date): ?Carbon
    {
        return match (strlen($date)) {
            8 => Carbon::createFromFormat("Ymd", $date),
            12 => Carbon::createFromFormat("YmdHi", $date),
            14 => Carbon::createFromFormat("YmdHis", $date),
            19 => Carbon::createFromFormat("YmdHisO", $date),
            default => null,
        };
    }

    //Todo use Encoding
    protected function lineToFields(): void
    {
        $this->resetData();
        $fields = preg_split('/(?<!\\\)\|/', $this->line);
        foreach ($fields as $k => $field) {
            $this->fieldToRepetitions($k, $field);
        }
    }

    protected function fieldToRepetitions(int $field_key, string $field): void
    {
        $repetitions = preg_split('/(?<!\\\)~/', $field);
        foreach ($repetitions as $k => $repetition) {
            $this->repetitionToComponents($k, $field_key, $repetition);
        }
    }

    public function repetitionToComponents(int $repetition_key, int $field_key, string $repetition): void
    {
        $components = preg_split('/(?<!\\\)\^/', $repetition);
        foreach ($components as $k => $component) {
            $this->componentToSubComponents($k, $repetition_key, $field_key, $component);
        }
    }

    public function componentToSubComponents(int $component_key, int $repetition_key, int $field_key, string $component): void
    {
        $subComponents = preg_split('/(?<!\\\)&/', $component);
        if (in_array("$field_key.$repetition_key.$component_key", array_keys($this->date_fields))) {
            $subComponents[0] = $this->toCarbon($subComponents[0]);
        }
        $this->data[$field_key][$repetition_key][$component_key] = $subComponents;
    }


    protected function setName(): void
    {
        $name = substr($this->line, 0, 3);
        if (!$this->name && $name) {
            $this->name = $name;
        }
    }


    protected function resetData(): void
    {
        $this->data = [];
        //foreach (range(0, 50) as $item) {
        //    $this->data[] = array_fill(0, 10, "");
        //}
    }

    private function expandData(int $field, int $repetition, int $component, int $subComponent): void
    {
        $this->data = array_pad($this->data, $field + 1, []);
        $this->data[$field] = array_pad($this->data[$field], $repetition + 1, []);
        $this->data[$field][$repetition] = array_pad($this->data[$field][$repetition], $component + 1, []);
        $this->data[$field][$repetition][$component] = array_pad($this->data[$field][$repetition][$component], $subComponent + 1, "");
    }
}