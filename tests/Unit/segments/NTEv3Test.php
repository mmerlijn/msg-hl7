<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\TestCode;

it('can read NTE', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->msg->comments[0]->text)->toBe($expectedRepo->msg->comments[0]->text);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L
",
        fn() => new Msg(
            comments: [new \mmerlijn\msgRepo\Comment(
                text: "Laboratorium",
                source: "P",
                type: new TestCode(
                    code: "ZD_CLUSTER_NAME",
                    value: "ZorgDomein clusternaam",
                    source: "L",
                ),
            )]
        )
    ],
]);
it('can write NTE', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $nte = new \mmerlijn\msgHl7\segments\NTE();
    $nte->setMsg($msg);
    $string = $nte->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => new Msg(
            comments: [new \mmerlijn\msgRepo\Comment(
                text: "Laboratorium",
                source: "P",
                type: new TestCode(
                    code: "ZD_CLUSTER_NAME",
                    value: "ZorgDomein clusternaam",
                    source: "L",
                ),
            )]
        ),
        "NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L"
    ],
]);


