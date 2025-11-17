<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

interface SegmentInterface
{
    public function read(string $line): self;

    //public function write(array $data):string;

    public function getMsg(Msg $msg): Msg;

    public function setMsg(Msg $msg): self;

    public function validate(): void;
}