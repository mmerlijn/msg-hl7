<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('can read IN1', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->patient->insurance->uzovi)->toBe($expectedRepo->patient->insurance->uzovi)
        ->and($msg->patient->insurance->policy_nr)->toBe($expectedRepo->patient->insurance->policy_nr)
        ->and($msg->patient->insurance->company_name)->toBe($expectedRepo->patient->insurance->company_name);
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
IN1|1|^null|0001^^^VEKTIS^UZOVI|ZorgVerzekeraar ZDNL||||||||||||||||||||||||||||||||12345678901234
",
        fn() => new Msg(
            patient: new \mmerlijn\msgRepo\Patient(
                insurance: new \mmerlijn\msgRepo\Insurance(
                    uzovi: "0001",
                    policy_nr: "12345678901234",
                    company_name: "ZorgVerzekeraar ZDNL",
                )
            )
        )
    ],
]);
it('can write IN1', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $in1 = new \mmerlijn\msgHl7\segments\IN1();
    $in1->setMsg($msg);
    $string = $in1->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => new Msg(
            patient: new \mmerlijn\msgRepo\Patient(
                insurance: new \mmerlijn\msgRepo\Insurance(
                    uzovi: "0001",
                    policy_nr: "12345678901234",
                    company_name: "ZorgVerzekeraar ZDNL",
                )
            )
        ),
        "IN1|1|^null|0001^^^VEKTIS^UZOVI|ZorgVerzekeraar ZDNL||||||||||||||||||||||||||||||||12345678901234"
    ],
]);


