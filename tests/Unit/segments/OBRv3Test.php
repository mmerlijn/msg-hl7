<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Msg;

it('can read OBR', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->where)->toBe($expectedRepo->order->where)
        ->and($msg->order->requester->agbcode)->toBe($expectedRepo->order->requester->agbcode)
        ->and($msg->order->requester->name->name)->toBe($expectedRepo->order->requester->name->name)
        ->and($msg->order->requests[0]->test->code)->toBe($expectedRepo->order->requests[0]->test->code)
        ->and($msg->order->requests[0]->test->name)->toBe($expectedRepo->order->requests[0]->test->name)
        ->and($msg->order->requests[0]->test->source)->toBe($expectedRepo->order->requests[0]->test->source)
        ->and($msg->order->requests[0]->other_test->code)->toBe($expectedRepo->order->requests[0]->other_test->code)
        ->and($msg->order->requests[0]->other_test->name)->toBe($expectedRepo->order->requests[0]->other_test->name)
        ->and($msg->order->requests[0]->other_test->source)->toBe($expectedRepo->order->requests[0]->other_test->source)
        ->and($msg->order->requests[0]->id)->toBe($expectedRepo->order->requests[0]->id);

})->with([

    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
OBR|2|GI2TIMZXHAYTEOI||ANSCRTOT^Anemie screening (HB / Controle)^L|||||||O||||BLD&Bloed&L|01123456^Blank^M.A.^^^^^^VEKTIS",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                request_nr: "ZP100120392",
                where: OrderWhereEnum::OTHER,
                requester: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "MA",
                        name: "Blank"
                    ),
                    source: "VEKTIS",
                ),
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        test: new \mmerlijn\msgRepo\TestCode(
                            code: "ANSCRTOT",
                            value: "Anemie screening (HB / Controle)",
                            source: "L"
                        ),
                        other_test: new \mmerlijn\msgRepo\TestCode(
                            code: "BLD",
                            value: "Bloed",
                            source: "L"
                        ),
                        id: "GI2TIMZXHAYTEOI"
                    )
                ]
            ),
        ),

    ],
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
OBR|1|ZP100120392||LABEDG001^laboratorium^L|||||||O|||||01123456^Blank^M.A.^^^^^^VEKTIS
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                where: OrderWhereEnum::OTHER,
                requester: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "MA",
                        name: "Blank"
                    ),
                    source: "VEKTIS",
                ),
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        test: new \mmerlijn\msgRepo\TestCode(
                            code: "LABEDG001",
                            value: "laboratorium",
                            source: "L"
                        ),
                        id: "ZP100120392"
                    )
                ]
            ),
        )
    ],
]);

it('can write OBR', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $obr = new \mmerlijn\msgHl7\segments\OBR();
    $obr->setMsg($msg);
    $string = $obr->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                where: OrderWhereEnum::OTHER,
                requester: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "MA",
                        name: "Blank"
                    ),
                    source: "VEKTIS",
                ),
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        test: new \mmerlijn\msgRepo\TestCode(
                            code: "LABEDG001",
                            value: "laboratorium",
                            source: "L",
                        ),
                        id: "ZP100120392",
                    )
                ]
            ),
        ),
        "OBR|1|ZP100120392||LABEDG001^laboratorium^L|||||||O|||||01123456^Blank^MA^^^^^^VEKTIS"
    ],
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                request_nr: "ZP100120392",
                where: OrderWhereEnum::OTHER,
                requester: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "MA",
                        name: "Blank"
                    ),
                    source: "VEKTIS",
                ),
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        test: new \mmerlijn\msgRepo\TestCode(

                            code: "ANSCRTOT",
                            value: "Anemie screening (HB / Controle)",
                            source: "L",
                        ),
                        other_test: new \mmerlijn\msgRepo\TestCode(
                            code: "BLD",
                            value: "Bloed",
                            source: "L",
                        ),
                        id: "GI2TIMZXHAYTEOI"
                    )
                ]
            ),
        ),
        "OBR|1|GI2TIMZXHAYTEOI||ANSCRTOT^Anemie screening (HB / Controle)^L|||||||O||||BLD&Bloed&L|01123456^Blank^MA^^^^^^VEKTIS",
    ],
]);

it('can add manual segments', function () {
    $msg = new Msg();
    $msg->setSegment('OBR.39.0.1', 'Test string');
    $obr = new \mmerlijn\msgHl7\segments\OBR();
    $obr->setMsg($msg);
    expect($obr->write())->toContain('Test string');
});
it('can read manual segments', function () {

});


