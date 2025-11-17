<?php

use mmerlijn\msgRepo\Msg;


it('can read patient details', function (string $hl7, \mmerlijn\msgRepo\Patient $expectedRepo) {
    $hl7v3 = new \mmerlijn\msgHl7\Hl7($hl7);
    $msgRepo = $hl7v3->getMsg(new Msg());
    expect($msgRepo->patient->ids[0]->id)->toBe($expectedRepo->ids[0]->id)
        ->and($msgRepo->patient->ids[0]->authority)->toBe($expectedRepo->ids[0]->authority)
        ->and($msgRepo->patient->ids[0]->code)->toBe($expectedRepo->ids[0]->code)
        ->and($msgRepo->patient->ids[1]->id)->toBe($expectedRepo->ids[1]->id)
        ->and($msgRepo->patient->ids[1]->authority)->toBe($expectedRepo->ids[1]->authority)
        ->and($msgRepo->patient->ids[1]->code)->toBe($expectedRepo->ids[1]->code)
        ->and($msgRepo->patient->name->initials)->toBe($expectedRepo->name->initials)
        ->and($msgRepo->patient->name->lastname)->toBe($expectedRepo->name->lastname)
        ->and($msgRepo->patient->name->prefix)->toBe($expectedRepo->name->prefix)
        ->and($msgRepo->patient->name->own_lastname)->toBe($expectedRepo->name->own_lastname)
        ->and($msgRepo->patient->name->own_prefix)->toBe($expectedRepo->name->own_prefix)
        ->and($msgRepo->patient->dob->format('Y-m-d'))->toBe($expectedRepo->dob->format('Y-m-d'))
        ->and($msgRepo->patient->sex->value)->toBe($expectedRepo->sex->value)
        ->and($msgRepo->patient->address->street)->toBe($expectedRepo->address->street)
        ->and($msgRepo->patient->address->building_nr)->toBe($expectedRepo->address->building_nr)
        ->and($msgRepo->patient->address->building_addition)->toBe($expectedRepo->address->building_addition)
        ->and($msgRepo->patient->address->city)->toBe($expectedRepo->address->city)
        ->and($msgRepo->patient->address->postcode)->toBe($expectedRepo->address->postcode)
        ->and($msgRepo->patient->address->country)->toBe($expectedRepo->address->country)
        ->and(count($msgRepo->patient->phones))->toBe(count($expectedRepo->phones))
        ->and($msgRepo->patient->phones[0]->number ?? "")->toBe($expectedRepo->phones[0]->number ?? "");
})->with([
   ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L
PID|1||900073962^^^NLMINBIZA^NNNLD~ZP100120391^^^ZorgDomein^VN||Testpatiënt - van ZorgDomein&van&ZorgDomein&&Testpatiënt^Z^D^^^^L||19901231|F|||2e Antonie Heinsiusstraat 3456 b&2e Antonie Heinsiusstraat&3456^b^'s-Gravenhage^^9999 ZZ^NL^M||+12025550144^PRN^PH~+31612345678^ORN^CP~^NET^Internet^demo@zorgdomein.nl||||||||||||||||||Y|NNNLD
",
       fn() => (new \mmerlijn\msgRepo\Patient(
           sex: \mmerlijn\msgRepo\Enums\PatientSexEnum::FEMALE,
           name: new \mmerlijn\msgRepo\Name(
               initials: "ZD",
               lastname: "Testpatiënt",
               prefix: "",
               own_lastname: "ZorgDomein",
               own_prefix: "van"
           ),
           dob: new \Carbon\Carbon("1990-12-31"),
           address: new \mmerlijn\msgRepo\Address(
               postcode: "9999 ZZ",
               city: "'s-Gravenhage",
               street: "2e Antonie Heinsiusstraat",
               building_nr: "3456",
               building_addition: "b",
               country: "NL"
           ),
           phones: [
               new \mmerlijn\msgRepo\Phone("+12025550144"),
               new \mmerlijn\msgRepo\Phone("+31612345678")
           ],
           ids: [
               new \mmerlijn\msgRepo\Id(
                   id: "900073962",
                   authority: "NLMINBIZA",
                   code: "NNNLD"
               ),
               new \mmerlijn\msgRepo\Id(
                   id: "ZP100120391",
                   authority: "ZorgDomein",
                   code: "VN"
               )
           ]
       ))
    ],

]);

it('can write patient details', function (\mmerlijn\msgRepo\Patient $patient, string $expectedPid) {
    $msg = new Msg();
    $msg->patient = $patient;

    $pid = new \mmerlijn\msgHl7\segments\PID();
    $pid->setMsg($msg);
    $string = $pid->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => (new \mmerlijn\msgRepo\Patient(
            sex: \mmerlijn\msgRepo\Enums\PatientSexEnum::MALE,
            name: new \mmerlijn\msgRepo\Name(
                initials: "J.D.",
                lastname: "Jansen",
                prefix: "de",
                own_lastname: "Pieters",
                own_prefix: "van"
            ),
            dob: new \Carbon\Carbon("1985-07-15"),
            address: new \mmerlijn\msgRepo\Address(
                postcode: "1234 AB",
                city: "Utrecht",
                street: "Kerkstraat",
                building_nr: "12",
                building_addition: "a",
                country: "NL"
            ),
            phones: [
                new \mmerlijn\msgRepo\Phone("+31698765432"),
            ],
            ids: [
                new \mmerlijn\msgRepo\Id(
                    id: "800112345",
                    authority: "NLMINBIZA",
                    code: "NNNLD"
                ),
                new \mmerlijn\msgRepo\Id(
                    id: "ZD800112345",
                    authority: "ZorgDomein",
                    code: "VN"
                )
            ],
            email: "demo@zorgdomein.nl"
        )),
        "PID|1||800112345^^^NLMINBIZA^NNNLD~ZD800112345^^^ZorgDomein^VN||de Jansen - van Pieters&van&Pieters&de&Jansen^J^D^^^^L||19850715|M|||Kerkstraat 12 a&Kerkstraat&12^a^Utrecht^^1234AB^NL||06 9876 5432^PRN^CP~^NET^Internet^demo@zorgdomein.nl||||||||||||||||||Y|NNNLD"
    ],
]);
