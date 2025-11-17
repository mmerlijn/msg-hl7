<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Msg;

it('can read SPM', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->requests[0]->other_test_code)->toBe($expectedRepo->order->requests[0]->other_test_code)
        ->and($msg->order->requests[0]->specimens[0]->test->code)->toBe($expectedRepo->order->requests[0]->specimens[0]->test->code)
        ->and($msg->order->requests[0]->specimens[0]->test->value)->toBe($expectedRepo->order->requests[0]->specimens[0]->test->value)
        ->and($msg->order->requests[0]->specimens[0]->test->source)->toBe($expectedRepo->order->requests[0]->specimens[0]->test->source)
        ->and($msg->order->requests[0]->specimens[0]->container->value)->toBe($expectedRepo->order->requests[0]->specimens[0]->container->value);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        id: "GI2TIMZXHAYTEOI",
                        specimens: [
                            new \mmerlijn\msgRepo\Specimen(
                                test: new \mmerlijn\msgRepo\TestCode(
                                    code: "BLD",
                                    value: "Bloed",
                                    source: "L"
                                ),
                                container: new \mmerlijn\msgRepo\TestCode(
                                    code: "",
                                    value: "Heparinebuis (01)",
                                    source: "L"
                                ),
                            )]
                    )
                ]
            ),
        ),
    ],
]);
it('can write SPM', function (\mmerlijn\msgRepo\Msg $msg, string $expected) {


    $spm = new \mmerlijn\msgHl7\segments\SPM();
    $spm->setMsg($msg);
    $string = $spm->write();

    expect($string)->toBe($expected);
})->with([
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        id: "GI2TIMZXHAYTEOI",
                        specimens: [
                            new \mmerlijn\msgRepo\Specimen(
                                test: new \mmerlijn\msgRepo\TestCode(
                                    code: "ORH",
                                    value: "Overige materialen",
                                    source: "L"
                                ),
                                container: new \mmerlijn\msgRepo\TestCode(
                                    code: "",
                                    value: "Navraag bij laboratorium",
                                    source: "L"
                                ),
                            )]
                    )
                ]
            ),
        ),
        "SPM|1|||ORH^Overige materialen^L||||||||||||||||N|||||||^Navraag bij laboratorium^L"
    ],
]);


