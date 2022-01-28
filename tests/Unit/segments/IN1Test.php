<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\segments\IN1;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;

class IN1Test extends \mmerlijn\msgHl7\tests\TestCase
{

    public function test_getter()
    {
        $in1 = new IN1("IN1|1|^null|0^^^LOCAL|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789");

        $msg = $in1->getMsg(new Msg());
        $this->assertSame("Ditzo Zorgverzekering", $msg->patient->insurance?->company_name);
        $this->assertSame("123456789", $msg->patient->insurance?->policy_nr);
    }

    public function test_setter()
    {
        $msg = new Msg();
        $msg->patient->setInsurance(new Insurance(company_name: "ABC", policy_nr: "12341234", uzovi: "123"));
        $string = (new Hl7())->setMsg($msg)->write();
        $this->assertStringContainsString("123^^^VEKTIS^UZOVI|ABC||||||||||||||||||||||||||||||||12341234", $string);
    }
}