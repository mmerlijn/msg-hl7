<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Observation;

it('can read Z03', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->requests[0]->specimens[0]->location)->toBe($expectedRepo->order->requests[0]->specimens[0]->location);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
Z03|SALT22
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        observations: [
                        ],
                        specimens: [
                            new \mmerlijn\msgRepo\Specimen(
                                test: new \mmerlijn\msgRepo\TestCode(
                                    code: "BCBB",
                                    value: "NIPT EDTA",
                                    source: "L"
                                ),
                                location: "SALT22",
                            )
                        ],
                    )
                ]
                )
        )
    ],
]);
it('can write Z03', function (\mmerlijn\msgRepo\Msg $msg, string $expected) {


    $z03 = new \mmerlijn\msgHl7\segments\Z03();
    $z03->setMsg($msg);
    $string = $z03->write();

    expect($string)->toBe($expected);
})->with([
    [
        fn() =>  new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        observations: [
                        ],
                        specimens: [
                            new \mmerlijn\msgRepo\Specimen(
                                test: new \mmerlijn\msgRepo\TestCode(
                                    code: "BCBB",
                                    value: "NIPT EDTA",
                                    source: "L"
                                ),
                                location: "SALT22",
                            )
                        ],
                    )
                ]
            )
        ),
        "Z03|SALT22"
    ],
]);


