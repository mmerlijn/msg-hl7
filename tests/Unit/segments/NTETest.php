<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\segments\NTE;
use mmerlijn\msgRepo\Msg;

class NTETest extends \mmerlijn\msgHl7\tests\TestCase
{
    public function test_setter()
    {
        $string = "Hello World!";
        $nte = new NTE();
        $nte->setComment(0, $string);
        $this->assertStringContainsString("1||$string", $nte->write());
    }

    public function test_getter()
    {
        $nte = new NTE("NTE|1||* Test text");
        $msg = $nte->getMsg(new Msg());
        $this->assertSame("* Test text", $msg->comments[0]);

    }
}