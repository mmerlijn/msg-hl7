<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use mmerlijn\msgHl7\segments\OBR;
use mmerlijn\msgHl7\segments\OBX;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Option;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;

class OBXTest extends \mmerlijn\msgHl7\tests\TestCase
{
    public function test_setter_string()
    {
        $msg = new Msg();
        $msg->order->addResult(new Result(value: "false", test_code: "ABC", test_name: "Alpha Beta Gamma", test_source: "99zdl", units: "mmol/l", reference_range: "1-4", done: true));
        $obx = new OBX();
        $obx->setResults($msg->order->results[0], $msg, 0);
        $this->assertStringContainsString("1|ST|ABC^Alpha Beta Gamma^99zdl||false|mmol/l|1-4||||F", $obx->write());
    }

    public function test_setter_array()
    {
        $msg = new Msg();
        $msg->order->addResult(new Result(test_code: "ABC", test_name: "Alpha Beta Gamma", test_source: "99zdl", units: "mmol/l", reference_range: "1-4", done: true, options: [
            new Option(label: "30", value: "Validated", source: "99zda"),
            new Option(label: "HARTVAAT", value: "Hart- en vaatziekten", source: "99zda"),
        ]));
        $obx = new OBX();
        $obx->setResults($msg->order->results[0], $msg, 0);
        $this->assertStringContainsString("1|CE|ABC^Alpha Beta Gamma^99zdl||30^Validated^99zda~HARTVAAT^Hart- en vaatziekten^99zda|mmol/l|1-4||||F", $obx->write());
    }

    public function test_getter()
    {
        $obx = new OBX("OBX|1|ST|ABC^Alpha Beta Gamma^99zdl||false||||||F");
        $msg = $obx->getMsg(new Msg());
        $this->assertSame("ABC", $msg->order->results[0]->test_code);
        $this->assertSame("Alpha Beta Gamma", $msg->order->results[0]->test_name);
        $this->assertSame("99zdl", $msg->order->results[0]->test_source);
        $this->assertSame("false", $msg->order->results[0]->value);
        $this->assertTrue($msg->order->results[0]->done);
        $this->assertFalse($msg->order->results[0]->change);

        $obx = new OBX("OBX|1|CE|ABC^Alpha Beta Gamma^99zdl||30^Validated^99zda~HARTVAAT^Hart- en vaatziekten^99zda||||||C");
        $msg = $obx->getMsg(new Msg());
        $this->assertSame("ABC", $msg->order->results[0]->test_code);
        $this->assertSame("Alpha Beta Gamma", $msg->order->results[0]->test_name);
        $this->assertSame("99zdl", $msg->order->results[0]->test_source);
        $this->assertSame("30", $msg->order->results[0]->value);
        $this->assertSame("Validated", $msg->order->results[0]->other_test_name);
        $this->assertSame("99zda", $msg->order->results[0]->other_test_source);
        $this->assertTrue($msg->order->results[0]->done);
        $this->assertTrue($msg->order->results[0]->change);
        $this->assertSame("Validated", $msg->order->results[0]->options[0]->value);
        $this->assertSame("30", $msg->order->results[0]->options[0]->label);
        $this->assertSame("99zda", $msg->order->results[0]->options[0]->source);
        $this->assertSame("Hart- en vaatziekten", $msg->order->results[0]->options[1]->value);
        $this->assertSame("HARTVAAT", $msg->order->results[0]->options[1]->label);
        $this->assertSame("99zda", $msg->order->results[0]->options[1]->source);
    }
}