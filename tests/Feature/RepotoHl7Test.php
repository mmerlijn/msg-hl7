<?php

namespace mmerlijn\msgHl7\tests\Feature;

use Carbon\Carbon;
use Exception;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;


it('create hl7', function () {
    $repo = new Msg();

    $repo->sender->application = "agendasalt";
    $repo->sender->facility = "SALT";
    $repo->receiver->application = "Mirth";
    $repo->receiver->facility = "Test";
    $repo->datetime = Carbon::now(); //default current datetime
    $repo->security_id = ""; //optional
    $repo->msgType->type = "ORM";
    $repo->msgType->trigger = "001";
    $repo->msgType->structure = "ORM_001";
    $repo->id = 123; //unique message id
    $repo->msgType->version = "2.5"; //default
    $repo->order->request_nr = "AB123";
    $repo->order->addRequest(new Request(test_code: "TST", test_name: "Testname", test_source: "SRC"));
    $repo->order->addResult(new Result(type_of_value: 'ST', value: 123, test_code: "TST", test_name: "Testname", test_source: "SRC"));
    $repo->order->control = OrderControlEnum::NEW;
    $hl7 = new Hl7();
    try {
        $hl7->setDatetimeFormat("YmdHis")
            ->setRepeatORC()
            ->setMsg($repo);

        $out = $hl7->write();
        expect($out)
            ->toContain("MSH", 'PID', 'ORC', "2.5", 'Mirth');

    } catch (Exception $e) {
        var_dump($e);
        die();
    }

});