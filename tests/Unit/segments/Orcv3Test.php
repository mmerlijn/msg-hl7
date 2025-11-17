<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('can read ORC', function (string $hl7, Msg $expectedRepo) {
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->control)->toBe($expectedRepo->order->control)
        ->and($msg->order->request_nr)->toBe($expectedRepo->order->request_nr)
        ->and($msg->order->priority)->toBe($expectedRepo->order->priority)
        ->and($msg->order->request_at->format('Y-m-d H:i:sP'))->toBe($expectedRepo->order->request_at->format('Y-m-d H:i:sP'))
        ->and($msg->order->requester->agbcode)->toBe($expectedRepo->order->requester->agbcode)
        ->and($msg->order->requester->name->name)->toBe($expectedRepo->order->requester->name->name)
        ->and($msg->order->requester->source)->toBe($expectedRepo->order->requester->source)
        ->and($msg->order->requester->location)->toBe($expectedRepo->order->requester->location)
        ->and($msg->order->organisation->name)->toBe($expectedRepo->order->organisation->name)
        ->and($msg->order->organisation->agbcode)->toBe($expectedRepo->order->organisation->agbcode)
        ->and($msg->order->organisation->source)->toBe($expectedRepo->order->organisation->source)
        ->and($msg->order->entered_by->agbcode)->toBe($expectedRepo->order->entered_by->agbcode)
        ->and($msg->order->entered_by->name->name)->toBe($expectedRepo->order->entered_by->name->name)
        ->and($msg->order->entered_by->source)->toBe($expectedRepo->order->entered_by->source)
        ->and($msg->sender->address->street)->toBe($expectedRepo->sender->address->street)
        ->and($msg->sender->address->building_nr)->toBe($expectedRepo->sender->address->building_nr)
        ->and($msg->sender->address->city)->toBe($expectedRepo->sender->address->city)
        ->and($msg->sender->address->postcode)->toBe($expectedRepo->sender->address->postcode)
        ->and($msg->sender->address->country)->toBe($expectedRepo->sender->address->country)
        ->and($msg->sender->phone->number)->toBe($expectedRepo->sender->phone->number)
        ;
})->with([
    ["MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
ORC|NW|ZP100120392||ZP100120392|||^^^^^R||20251112125346+0100|01123456^Blank^M.A.^^^^^^VEKTIS||01123456^Blank^M.A.^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog aan de Zaan^^1541WR^NL|0889100100^WPN^PH
",
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                control: \mmerlijn\msgRepo\Enums\OrderControlEnum::NEW,
                request_nr: "ZP100120392",
                priority: false,
                requester: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "M.A.",
                        name: "Blank"
                    ),
                    source: "VEKTIS",
                    location: "SALT"
                ),
                entered_by: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "M.A.",
                        name: "Blank"
                    ),
                    source: "VEKTIS"
                ),
                organisation: new \mmerlijn\msgRepo\Organisation(
                    name: "SALT",
                    agbcode: "50009046",
                    source: "VEKTIS"
                ),
                request_at: new \Carbon\Carbon("2025-11-12 12:53:46+01:00"),
            ),
            sender: new \mmerlijn\msgRepo\Contact(
                address: new \mmerlijn\msgRepo\Address(
                    postcode: "1541WR",
                    city: "Koog aan de Zaan",
                    street: "Molenwerf",
                    building_nr: "11",
                    country: "NL"
                ),
                phone:
                    new \mmerlijn\msgRepo\Phone(
                        number: "0889100100"
                    )

            )
        ),

    ],
]);
it('can write ORC', function (\mmerlijn\msgRepo\Msg $msg, string $expectedPid) {


    $orc = new \mmerlijn\msgHl7\segments\ORC();
    $orc->setMsg($msg);
    $string = $orc->write();

    expect($string)->toBe($expectedPid);
})->with([
    [
        fn() => new Msg(
            order: new \mmerlijn\msgRepo\Order(
                control: \mmerlijn\msgRepo\Enums\OrderControlEnum::NEW,
                request_nr: "ZP100120392",
                priority: false,
                requester: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "M.A.",
                        name: "Blank"
                    ),
                    source: "VEKTIS",
                    location: "SALT"
                ),
                entered_by: new \mmerlijn\msgRepo\Contact(
                    agbcode: "01123456",
                    name: new \mmerlijn\msgRepo\Name(
                        initials: "M.A.",
                        name: "Blank"
                    ),
                    source: "VEKTIS"
                ),
                organisation: new \mmerlijn\msgRepo\Organisation(
                    name: "SALT",
                    agbcode: "50009046",
                    source: "VEKTIS"
                ),
                request_at: new \Carbon\Carbon("2025-11-12 12:53:46+01:00"),
            ),
            sender: new \mmerlijn\msgRepo\Contact(
                address: new \mmerlijn\msgRepo\Address(
                    postcode: "1541WR",
                    city: "Koog aan de Zaan",
                    street: "Molenwerf",
                    building_nr: "11",
                    country: "NL"
                ),
                phone:
                new \mmerlijn\msgRepo\Phone(
                    number: "0889100100"
                )

            )
        ),
        "ORC|NW|ZP100120392||ZP100120392|||^^^^^R||20251112125346+0100|01123456^Blank^MA^^^^^^VEKTIS||01123456^Blank^MA^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog Aan De Zaan^^1541WR^NL|088 9100 100^WPN^PH"
    ],
]);


