<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Order;
use mmerlijn\msgRepo\TestCode;

it('can read PV2', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order)
        ->admit_reason->name->toBe($expectedRepo->order->admit_reason->name)
        ->admit_reason->code->toBe($expectedRepo->order->admit_reason->code);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
PV2|||LABEDG001^laboratorium^L
",
        fn() => new Msg(
            order: new Order(
                admit_reason: new TestCode(code: "LABEDG001", value: "laboratorium")
            )
        )
    ],
]);

it('can write PV2', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $pv2 = new \mmerlijn\msgHl7\segments\PV2();
    $pv2->setMsg($msg);
    $string = $pv2->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        new Msg(
            order: new Order(
                admit_reason: new TestCode(code: "LABEDG001", value: "laboratorium")
            )
        ),
        "PV2|||LABEDG001^laboratorium^L"
    ],
]);

