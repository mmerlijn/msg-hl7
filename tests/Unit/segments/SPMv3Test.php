<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Msg;

it('can read SPM', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->requests[0]->other_test->code)->toBe($expectedRepo->order->requests[0]->other_test->code)
        ->and($msg->order->requests[0]->specimens[0]->type->code)->toBe($expectedRepo->order->requests[0]->specimens[0]->type->code)
        ->and($msg->order->requests[0]->specimens[0]->type->value)->toBe($expectedRepo->order->requests[0]->specimens[0]->type->value)
        ->and($msg->order->requests[0]->specimens[0]->type->source)->toBe($expectedRepo->order->requests[0]->specimens[0]->type->source)
        ->and($msg->order->requests[0]->specimens[0]->container->value)->toBe($expectedRepo->order->requests[0]->specimens[0]->container->value)
        ->and($msg->order->observation_at->format('Y-m-d H:i:s'))->toBe(\Carbon\Carbon::make("2026-01-27 17:48:48")->format('Y-m-d H:i:s'))
    ;

})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
SPM|1|||BLD^Bloed^L|||||||||||||20260127174848|||N|||||||^Heparinebuis (01)^L
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        id: "GI2TIMZXHAYTEOI",
                        specimens: [
                            new \mmerlijn\msgRepo\Specimen(
                                type: new \mmerlijn\msgRepo\TestCode(
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
    $string = $spm->setDatetimeFormat("YmdHis")->write();

    expect($string)->toBe($expected);
})->with([
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                observation_at: \Carbon\Carbon::make('2026-01-27 17:48:48'),
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        id: "GI2TIMZXHAYTEOI",
                        specimens: [
                            new \mmerlijn\msgRepo\Specimen(
                                type: new \mmerlijn\msgRepo\TestCode(
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
        "SPM|1|||ORH^Overige materialen^L|||||||||||||20260127174848|||N|||||||^Navraag bij laboratorium^L"
    ],
]);


