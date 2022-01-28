<?php

namespace mmerlijn\msgHl7\segments;


use mmerlijn\msgRepo\Msg;

class Segment implements SegmentInterface
{
    public $repeat = false; //default segment is not repeatable

    public string $name;    //name of the segment
    public array $data;     //data of the segment (multi dimensional)

    public function __construct(public string $line = "")
    {
        $this->resetData();
        if ($line) {
            $this->setName();
            $this->lineToFields();
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

    public function write(): string
    {
        $fields = [];
        foreach ($this->data as $i) {
            $rep = [];
            foreach ($i as $j) {
                $components = [];
                foreach ($j as $k) {
                    $components[] = implode("&", $k);
                }
                $rep[] = implode("^", $components);
            }
            $fields[] = implode("~", $rep);
        }
        return implode("|", $fields);
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

    public function setData(mixed $value, int $field, int $repetition = 0, int $component = 0, int $subComponent = 0): self
    {
        if (!($this->data[$field][$repetition][$component][$subComponent] ?? false)) {
            $this->expandData($field, $repetition, $component, $subComponent);
        }
        $this->data[$field][$repetition][$component][$subComponent] = preg_replace('/(\||~|\^|\&)/', '\$1', $value ?? "");
        return $this;
    }


    public function getData(int $field, int $repetition = 0, int $component = 0, int $subComponent = 0): string
    {
        return preg_replace('/\\\(\||~|\^|&)/', '$1', $this->data[$field][$repetition][$component][$subComponent] ?? "");
    }

    //Todo use Encoding
    protected function lineToFields()
    {
        $this->resetData();
        $fields = preg_split('/(?<!\\\)\|/', $this->line);
        foreach ($fields as $k => $field) {
            $this->fieldToRepetitions($k, $field);
        }
    }

    protected function fieldToRepetitions(int $field_key, string $field)
    {
        $repetitions = preg_split('/(?<!\\\)~/', $field);
        foreach ($repetitions as $k => $repetition) {
            $this->repetitionToComponents($k, $field_key, $repetition);
        }
    }

    public function repetitionToComponents(int $repetition_key, int $field_key, string $repetition)
    {
        $components = preg_split('/(?<!\\\)\^/', $repetition);
        foreach ($components as $k => $component) {
            $this->componentToSubComponents($k, $repetition_key, $field_key, $component);
        }
    }

    public function componentToSubComponents(int $component_key, int $repetition_key, int $field_key, string $component)
    {
        $subComponents = preg_split('/(?<!\\\)&/', $component);
        $this->data[$field_key][$repetition_key][$component_key] = $subComponents;
    }


    protected function setName()
    {
        $this->name = substr($this->line, 0, 3);
    }


    protected function resetData()
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