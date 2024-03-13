<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\segments\OBR;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Request;

class OBRTest extends \mmerlijn\msgHl7\tests\TestCase
{
    public function test_setter()
    {
        $msg = new Msg();
        $msg->order->request_nr = "AB12341234";
        $msg->order->addRequest(new Request(test_code: "ABC", test_name: "Alpha Beta Gamma", test_source: "99zdl"));
        $msg->order->where = OrderWhereEnum::set("home");
        $msg->order->requester->setName(new Name(name: "van der Plas", initials: "R."));
        $msg->order->requester->source = "VEKTIS";
        $msg->order->requester->agbcode = "01123456";
        $obr = new OBR();
        $obr->setRequest($msg, 0);
        $this->assertStringContainsString("AB12341234||ABC^Alpha Beta Gamma^99zdl|R||||||L|||||01123456^van der Plas^R^^^^^^VEKTIS", $obr->write());
    }

    public function test_where_setter()
    {
        $msg = new Msg();
        $msg->order->where = OrderWhereEnum::HOME;
        $msg->order->addRequest(new Request(test_code: "ABC", test_name: "Alpha Beta Gamma", test_source: "99zdl"));
        $obr = new OBR();
        $obr->setRequest($msg, 0);
        $this->assertStringContainsString("|R||||||L", $obr->write());
        $msg->order->where = OrderWhereEnum::OTHER;
        $msg->order->addRequest(new Request(test_code: "ABC", test_name: "Alpha Beta Gamma", test_source: "99zdl"));
        $obr = new OBR();
        $obr->setRequest($msg, 0);
        $this->assertStringContainsString("|R||||||O", $obr->write());
    }

    public function test_getter()
    {
        $obr = new OBR("OBR|1|ZD12345678||ABC^CRP^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS");
        $msg = $obr->getMsg(new Msg());
        $this->assertSame("OTHER", $msg->order->where->value);
        $this->assertSame("ZD12345678", $msg->order->request_nr);
        $this->assertSame("ABC", $msg->order->requests[0]->test_code);
        $this->assertSame("CRP", $msg->order->requests[0]->test_name);
        $this->assertSame("99zdl", $msg->order->requests[0]->test_source);
        $this->assertSame("01123456", $msg->order->requester->agbcode);
        $this->assertSame("R", $msg->order->requester->name->initials);
        $this->assertSame("van der", $msg->order->requester->name->own_prefix);
        $this->assertSame("Plas", $msg->order->requester->name->own_lastname);
        $this->assertSame("VEKTIS", $msg->order->requester->source);

    }
}