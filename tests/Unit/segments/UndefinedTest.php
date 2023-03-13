<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\segments\IN1;
use mmerlijn\msgHl7\segments\Undefined;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class UndefinedTest extends \mmerlijn\msgHl7\tests\TestCase
{

    public function test_getter()
    {
        $undefined = new Undefined("BLG||CH");
        $msg_start = new Msg();
        $msg = $undefined->getMsg($msg_start);
        $this->assertEquals($msg, $msg_start);
    }


}