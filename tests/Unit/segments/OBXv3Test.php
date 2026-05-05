<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Observation;
use mmerlijn\msgRepo\TestCode;

it('can read OBX', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->requests[0]->observations[0]->value)->toBe($expectedRepo->order->requests[0]->observations[0]->value)
        ->and($msg->order->requests[0]->observations[0]->test->code)->toBe($expectedRepo->order->requests[0]->observations[0]->test->code)
        ->and($msg->order->requests[0]->observations[0]->test->value)->toBe($expectedRepo->order->requests[0]->observations[0]->test->value)
        ->and($msg->order->requests[0]->observations[0]->done)->toBe($expectedRepo->order->requests[0]->observations[0]->done)
        ->and($msg->order->requests[0]->observations[1]->test->value)->toBe($expectedRepo->order->requests[0]->observations[1]->test->value)
        ->and($msg->order->requests[0]->observations[1]->test->code)->toBe($expectedRepo->order->requests[0]->observations[1]->test->code)
        ->and($msg->order->requests[0]->observations[1]->values[0]->value)->toBe($expectedRepo->order->requests[0]->observations[1]->values[0]->value)
        ->and($msg->order->requests[0]->observations[2]->values[0]->value)->toBe($expectedRepo->order->requests[0]->observations[2]->values[0]->value)
    ;
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
OBX|1|FT|AI^Opmerkingen / klinische gegevens^L||ontsteking?||||||F
OBX|2|CE|^Prostaatkanker^99zda||^Nee||||||F
OBX|3|CE|^Borstkanker^99zda||^Nee||||||F
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [new \mmerlijn\msgRepo\Request(
                    observations: [
                        new Observation(
                            value: "ontsteking?",
                            test: new TestCode(
                                code: "AI",
                                value: "Opmerkingen / klinische gegevens",
                                source: "L",
                            ),
                            done: true,
                        ),
                        new Observation(
                            value: "",
                            test: new TestCode(
                                code: "",
                                value: "Prostaatkanker",
                                source: "99zda",
                            ),
                            done: true,
                            values: [new TestCode(value:"Nee")],
                        ),
                        new Observation(
                            value: "",
                            test: new TestCode(
                                code: "",
                                value: "Borstkanker",
                                source: "99zda",
                            ),
                            done: true,
                            values: [new TestCode(value:"Nee")],
                        ),
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
                        new Observation(
                            type: \mmerlijn\msgRepo\Enums\ValueTypeEnum::FT,
                            value: "ontsteking?",
                            test: new TestCode(
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


