<?php

use Carbon\Carbon;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('can read BLG', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->toArray())->toBe($expectedRepo->toArray());
})->with([
    ["MSH|^~\&|||||20251112125133+0100||OML^O21^OML_O21|||2.5.1|||||NLD|8859/1
BLG||CH
",
        fn() => new Msg(
            datetime: Carbon::create('2025-11-12 12:51:33+01:00'),
            msgType: new \mmerlijn\msgRepo\MsgType(
                type: "OML",
                trigger: "O21",
                structure: "OML_O21",
                version: "2.5.1",
                charset: "8859/1"
            )
        )
    ],
]);
it('can write BLG', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $blg = new \mmerlijn\msgHl7\segments\BLG();
    $blg->setMsg($msg);
    $string = $blg->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => new Msg(

        ),
        "BLG||CH"
    ],
]);


