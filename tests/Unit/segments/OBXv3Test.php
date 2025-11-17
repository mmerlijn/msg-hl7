<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('can read OBX', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->requests[0]->observations[0]->value)->toBe($expectedRepo->order->requests[0]->observations[0]->value)
        ->and($msg->order->requests[0]->observations[0]->test->code)->toBe($expectedRepo->order->requests[0]->observations[0]->test->code)
        ->and($msg->order->requests[0]->observations[0]->test->name)->toBe($expectedRepo->order->requests[0]->observations[0]->test->name)
        ->and($msg->order->requests[0]->observations[0]->done)->toBe($expectedRepo->order->requests[0]->observations[0]->done);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
OBX|1|FT|AI^Opmerkingen / klinische gegevens^L||ontsteking?||||||F
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [new \mmerlijn\msgRepo\Request(
                    observations: [
                        new \mmerlijn\msgRepo\Observation(
                            value: "ontsteking?",
                            test: new \mmerlijn\msgRepo\TestCode(
                                code: "AI",
                                value: "Opmerkingen / klinische gegevens",
                                source: "L",
                            ),
                            done: true,
                        )
                    ])
                ]
            )
        )
    ],
]);
it('can write OBX', function (\mmerlijn\msgRepo\Msg $msg, string $expected) {


    $obx = new \mmerlijn\msgHl7\segments\OBX();
    $obx->setMsg($msg);
    $string = $obx->write();

    expect($string)->toBe($expected);
})->with([
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [new \mmerlijn\msgRepo\Request(
                    observations: [
                        new \mmerlijn\msgRepo\Observation(
                            type: \mmerlijn\msgRepo\Enums\ValueTypeEnum::FT,
                            value: "ontsteking?",
                            test: new \mmerlijn\msgRepo\TestCode(
                                code: "AI",
                                value: "Opmerkingen / klinische gegevens",
                                source: "L",
                            ),
                            done: true,
                        )
                    ])
                ]
            )
        ),
        "OBX|1|FT|AI^Opmerkingen / klinische gegevens^L||ontsteking?||||||F"
    ],
]);


