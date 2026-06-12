<?php

namespace mmerlijn\msgHl7\tests\Feature;

use Carbon\Carbon;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Patient;
use mmerlijn\msgRepo\Specimen;

it('Read HL7', function (string $dataset, Msg $expected) {
    $hl7 = new Hl7($dataset);
    $hl7->getSegment('PID.1');
    $msgRepo = $hl7->getMsg(new Msg());


    expect($msgRepo->order->request_nr)->toBe($expected->order->request_nr)
        ->and($msgRepo->patient->bsn)->toBe($expected->patient->bsn)
        ->and($msgRepo->patient->name->own_lastname)->toBe($expected->patient->name->own_lastname)
        ->and($msgRepo->patient->dob->format("Y-m-d"))->toBe($expected->patient->dob->format("Y-m-d"))
        ->and($msgRepo->patient->phones[0]->number)->toBe($expected->patient->phones[0]->number)
        ->and($msgRepo->patient->phones[1]->number)->toBe($expected->patient->phones[1]->number)
        ->and($msgRepo->patient->address)
        ->street->toBe($expected->patient->address->street)
        ->city->toBe($expected->patient->address->city)
        ->building->toBe($expected->patient->address->building)
        ->postcode->toBe($expected->patient->address->postcode)
        ->country->toBe($expected->patient->address->country)
        ->and($msgRepo->order->priority)->toBe($expected->order->priority)
        ->and($msgRepo->order->requests[0]->observations[0]->test->code)->toBe($expected->order->requests[0]->observations[0]->test->code)
        ->and($msgRepo->order->requests[0]->observations[0]->value)->toBe($expected->order->requests[0]->observations[0]->value)
        ->and($msgRepo->order->requests[1]->test->code)->toBe($expected->order->requests[1]->test->code)
        ->and($msgRepo->order->requests[1]->specimens[0]->test->code)->toBe($expected->order->requests[1]->specimens[0]->test->code)
        ->and($msgRepo->order->requests[1]->specimens[0]->container->value)->toBe($expected->order->requests[1]->specimens[0]->container->value)
        ->and($msgRepo->order->requests[1]->id)->toBe($expected->order->requests[1]->id)
        ->and($msgRepo->getSegment("PID.1"))->toBe('1');


})->with([
    "zorgdomein1" => [
        "MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L
PID|1||900073962^^^NLMINBIZA^NNNLD~ZP100120391^^^ZorgDomein^VN||Testpatiënt - van Zorg Domein&van&Zorg Domein&&Testpatiënt^Z^D^^^^L||19901231|F|||2e Antonie Heinsiusstraat 3456 b&2e Antonie Heinsiusstraat&3456^b^'s-Gravenhage^^9999 ZZ^NL^M||+12025550144^PRN^PH~+31612345678^ORN^CP~^NET^Internet^demo@zorgdomein.nl||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^L
IN1|1|^null|0001^^^VEKTIS^UZOVI|ZorgVerzekeraar ZDNL||||||||||||||||||||||||||||||||12345678901234
ORC|NW|ZP100120391||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
TQ1|1||||||||R^Routine^HL70485
OBR|1|ZP100120391||LABEDG001^laboratorium^L|||||||O|||||01123456^Blank^M.A.^^^^^^VEKTIS
ORC|NW|FUYTMNJYGA3TKNRWGA||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
TQ1|2||||||||R^Routine^HL70485
OBR|2|FUYTMNJYGA3TKNRWGA||ALAT^ALAT^L|||||||O||||BLD&Bloed&L|01123456^Blank^M.A.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L
ORC|NW|FUYTOMJVGI3DSMRQGI||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
TQ1|3||||||||R^Routine^HL70485
OBR|3|FUYTOMJVGI3DSMRQGI||ALB^Albumine^L|||||||O||||BLD&Bloed&L|01123456^Blank^M.A.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L
ORC|NW|FU3DSNJYHEZDKNZV||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
TQ1|4||||||||R^Routine^HL70485
OBR|4|FU3DSNJYHEZDKNZV||MALB^Albumine (micro) urine portie (ACR)^L|||||||O||||UR&Urine&L|01123456^Blank^M.A.^^^^^^VEKTIS
SPM|1|||UR^Urine^L||||||||||||||||N|||||||^Urine (02)^L
ORC|NW|FU3TQNJQHE2DMNRR||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
TQ1|5||||||||R^Routine^HL70485
OBR|5|FU3TQNJQHE2DMNRR||CRP^CRP^L|||||||O||||BLD&Bloed&L|01123456^Blank^M.A.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L",
        new Msg(patient: new Patient(
            name: new \mmerlijn\msgRepo\Name(
                initials: "ZD",
                prefix: "van",
                own_lastname: "Zorg Domein",
            ),
            dob: Carbon::create("1990-12-31"),
            address: new \mmerlijn\msgRepo\Address(
                postcode: "9999ZZ",
                city: "'s-Gravenhage",
                street: "2e Antonie Heinsiusstraat",
                building: "3456 b",
                country: "NL"
            ),
            phones: [
                new \mmerlijn\msgRepo\Phone(number: "+12025550144"),
                new \mmerlijn\msgRepo\Phone(number: "0612345678"),
            ],
            ids: [
                new \mmerlijn\msgRepo\Id(id: '900073962', authority: "NLMINBIZA", type: 'bsn', code: "NNNLD"),
                new \mmerlijn\msgRepo\Id(id: 'ZP100120391', authority: "zorgdomein", code: "VN"),
            ]
        ),
            order: new \mmerlijn\msgRepo\Order(
                request_nr: "ZP100120391",
                priority: false,
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        test: new \mmerlijn\msgRepo\TestCode(
                            code: "LABEDG001",
                            value: "laboratorium",
                            source: "L"
                        ),
                    //priority: false,
                    ),
                    new \mmerlijn\msgRepo\Request(
                        test: new  \mmerlijn\msgRepo\TestCode(
                            code: "ALAT",
                            value: "ALAT",
                            source: "L"
                        ),
                        id: "FUYTMNJYGA3TKNRWGA",
                        specimens: [
                            new Specimen(
                                type: new \mmerlijn\msgRepo\TestCode(
                                    code: "BLD",
                                    value: "Bloed",
                                    source: "L"
                                ),
                                container: new \mmerlijn\msgRepo\TestCode(
                                    code: "",
                                    value: "Heparinebuis (01)",
                                    source: "L"
                                )
                            )
                        ]
                    ),
                ],
            )
        )],
    ["MSH|^~\&|ZorgDomein||OrderModule||20240417172619+0200||ORM^O01^ORM_O01|60d2b5bb2af84273941a|P|2.4|||||NLD|8859/1
PID|1||900073962^^^NLMINBIZA^NNNLD~ZP100120391^^^ZorgDomein^VN||Testpatiënt - van Zorg Domein&van&Zorg Domein&&Testpatiënt^Z^D^^^^L||19901231|F|||2e Antonie Heinsiusstraat 3456 b&2e Antonie Heinsiusstraat&3456^b^'s-Gravenhage^^9999 ZZ^NL^M||+12025550144^PRN^PH~+31612345678^ORN^CP~^NET^Internet^demo@zorgdomein.nl||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^L
IN1|1|^null|0001^^^VEKTIS^UZOVI|ZorgVerzekeraar ZDNL||||||||||||||||||||||||||||||||12345678901234
ORC|NW|ZP100120391||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
OBR|1|ZP100120391||SPIROM^Spirometrie Diagnostiek^99zdl|||||||O|||||01123456^Blank^M.A.^^^^^^VEKTIS
OBX|1|ST|REDE^Reden van aanvraag^99zda||onbegrepen benauwdheid, pijn links thoracaal en in de ochtend een piepende ademhaling. Niet bekend met atsma of allergie\.br\heeft long covid.\.br\Ook psychosociale problematiek wat mgl van invloed is||||||F
OBX|2|ST|ANAM^Anamnese^99zda||Heeft pijn bovenbuik.||||||F
OBX|3|CE|ZWAN^Zwangerschap^99zda||n^Nee^99zda||||||F
ORC|NW|ZP100120391||ZP100120391|||^^^^^R||20251112125108+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
OBR|2|ZP100120391||TIJD^TIJD^99zdl|||||||O|||||01123456^Blank^M.A.^^^^^^VEKTIS",
        new Msg(
            patient: new Patient(
                name: new \mmerlijn\msgRepo\Name(
                    initials: "ZD",
                    prefix: "van",
                    own_lastname: "Zorg Domein",
                ),
                dob: Carbon::create("1990-12-31"),
                address: new \mmerlijn\msgRepo\Address(
                    postcode: "9999ZZ",
                    city: "'s-Gravenhage",
                    street: "2e Antonie Heinsiusstraat",
                    building: "3456 b",
                    country: "NL"
                ),
                phones: [
                    new \mmerlijn\msgRepo\Phone(number: "+12025550144"),
                    new \mmerlijn\msgRepo\Phone(number: "0612345678"),
                ],
                ids: [
                    new \mmerlijn\msgRepo\Id(id: '900073962', authority: "NLMINBIZA", type: 'bsn', code: "NNNLD"),
                    new \mmerlijn\msgRepo\Id(id: 'ZP100120391', authority: "zorgdomein", code: "VN"),
                ]
            ),
            order: new \mmerlijn\msgRepo\Order(
                request_nr: "ZP100120391",
                priority: false,
                requests: [
                    new \mmerlijn\msgRepo\Request(
                        test: new \mmerlijn\msgRepo\TestCode(
                            code: "SPIROM",
                            value: "Spirometrie Diagnostiek",
                            source: "99zdl"
                        ),
                        observations: [
                            new \mmerlijn\msgRepo\Observation(
                                value: "onbegrepen benauwdheid, pijn links thoracaal en in de ochtend een piepende ademhaling. Niet bekend met atsma of allergie".PHP_EOL."heeft long covid.".PHP_EOL."Ook psychosociale problematiek wat mgl van invloed is",
                                test: new \mmerlijn\msgRepo\TestCode(
                                    code: "REDE",
                                    value: "Reden van aanvraag",
                                    source: "99zda"
                                )
                            ),
                            new \mmerlijn\msgRepo\Observation(
                                value: "Heeft pijn bovenbuik.",
                                test: new \mmerlijn\msgRepo\TestCode(
                                    code: "ANAM",
                                    value: "Anamnese",
                                    source: "99zda"
                                )
                            ),
                        ]
                    ),
                    new \mmerlijn\msgRepo\Request(
                        test: new  \mmerlijn\msgRepo\TestCode(
                            code: "TIJD",
                            value: "TIJD",
                            source: "99zdl"
                        ),
                        id: "ZP100120391",
                    )])
        )],
]);





