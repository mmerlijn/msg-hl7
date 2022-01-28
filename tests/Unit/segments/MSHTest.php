<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

class MSHTest extends \mmerlijn\msgHl7\tests\TestCase
{
    public function test_setters()
    {
        $msg = new Msg();
        $msg->sender->application = "Application";
        $msg->sender->facility = "Facility";
        $msg->receiver->application = "rcApplication";
        $msg->receiver->facility = "rcFacility";

        $msg->msgType->version = "2.5";
        $msg->msgType->structure = "ORU_002";
        $msg->msgType->trigger = "002";
        $msg->msgType->type = "ORM";
        $msg->id = "Q123";
        $hl7 = (new Hl7())->setMsg($msg);
        //var_dump($hl7->segments[0]->data);
        $string = $hl7->write();
        $this->assertStringContainsString("|Application|Facility|rcApplication|rcFacility|", $string);
        $this->assertStringContainsString(date("YmdHisO"), $string);
        $this->assertStringContainsString("|ORM^002^ORU_002|Q123", $string);
        $this->assertStringContainsString("|2.5|", $string);

    }

    public function test_getter()
    {

        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("ZorgDomein", $msg->sender->application);
        $this->assertSame("OrderModule", $msg->receiver->application);
        $this->assertSame("20220102161545+0200", $msg->datetime->format('YmdHisO'));
        $this->assertSame("ORM", $msg->msgType->type);
        $this->assertSame("O01", $msg->msgType->trigger);
        $this->assertSame("ORM_O01", $msg->msgType->structure);
        $this->assertSame("e49ce31d", $msg->id);
        $this->assertSame("P", $msg->processing_id);
        $this->assertSame("2.4", $msg->msgType->version);

    }
}