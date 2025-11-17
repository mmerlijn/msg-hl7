<?php

use mmerlijn\msgRepo\Msg;

it('can read PV1', function (string $hl7, Msg $expectedRepo) {
    $hl7v3 = new \mmerlijn\msgHl7\Hl7($hl7);
    $msgRepo = $hl7v3->getMsg(new Msg());
    expect($msgRepo)->toBeInstanceOf(Msg::class)
        ->and($msgRepo->toArray())->toEqual($expectedRepo->toArray());
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
",
        fn() => new Msg(
            sender: ['application' => 'ZorgDomein'],
            receiver: ['application' => 'Labtrain', 'facility' => 'SALT'],
            datetime: '2025-11-12 12:51:33+01:00',
            msgType: new \mmerlijn\msgRepo\MsgType(
                type: 'OML',
                trigger: 'O21',
                structure: 'OML_O21',
                version: '2.5.1',
                charset: '8859/1',
            ),
            id: 'd0a06274854e4824a8a4',
            processing_id: 'P',
        )
    ],
]);

it('can write PV1', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $pv1 = new \mmerlijn\msgHl7\segments\PV1();
    $pv1->setMsg($msg);
    $string = $pv1->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        new Msg(),
        "PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V"
    ],
]);