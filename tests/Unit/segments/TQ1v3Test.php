<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('can read TQ1', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->priority)->toBe($expectedRepo->order->priority);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
TQ1|1||||||||R^Routine^HL70485
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                priority: false,
                requests: [
                    new \mmerlijn\msgRepo\Request(

                    )
                ]
            )
        )
    ],
]);

it('can write TQ1', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $tq1 = new \mmerlijn\msgHl7\segments\TQ1();
    $tq1->setMsg($msg);
    $string = $tq1->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                priority: false,
            ),
        ),
        "TQ1|1||||||||R^Routine^HL70485"
    ],

]);


