<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

class PV2Test extends \mmerlijn\msgHl7\tests\TestCase
{
    public function test_getter()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PV2|||CODE001^lab^99zda");

        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("CODE001", $msg->order->admit_reason_code);
        $this->assertSame("lab", $msg->order->admit_reason_name);
    }

    public function test_setter()
    {
        $msg = new Msg();
        $msg->order->admit_reason_code = "ABC123";
        $msg->order->admit_reason_name = "LAB";
        $string = (new Hl7())->setMsg($msg)->write();
        $this->assertStringContainsString("ABC123^LAB^99zda", $string);
    }
}