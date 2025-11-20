<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Contact;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Organisation;

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
        ->and($msg->sender->phone->number)->toBe($expectedRepo->sender->phone->number);
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
                organisation: new Organisation(
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
                phone: new \mmerlijn\msgRepo\Phone(
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
                organisation: new Organisation(
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
                phone: new \mmerlijn\msgRepo\Phone(
                    number: "0889100100"
                )

            )
        ),
        "ORC|NW|ZP100120392||ZP100120392|||^^^^^R||20251112125346+0100|01123456^Blank^MA^^^^^^VEKTIS||01123456^Blank^MA^^^^^^VEKTIS|^^^^^^^^SALT||||50009046^SALT^VEKTIS||||SALT^^50009046^^^VEKTIS|Molenwerf 11&Molenwerf&11^^Koog Aan De Zaan^^1541WR^NL|088 9100 100^WPN^PH"
    ],
]);

it('can read requester and organisation', function (string $orc, Contact $requester, Organisation $organisation) {
    $hl7 = new Hl7("MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1
" . $orc);
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order->organisation->name)->toBe($organisation->name)
        ->and($msg->order->organisation->agbcode)->toBe($organisation->agbcode)
        ->and($msg->order->organisation->source)->toBe($organisation->source)
        ->and($msg->order->requester->name)->toMatchObject($requester->name)
        ->and($msg->order->requester->agbcode)->toBe($requester->agbcode);
})->with([
    ["ORC|NW|ZP12345||ZP12345|||^^^^^R||20251212113429+0100|^Joe^s||01234567^Doe^J^^^^^^VEKTIS|^^^HP Doe&07654321^^^^^HP Doe||||07654321^HP Doe^VEKTIS||||HP Doe^^07654321^^^VEKTIS",
        fn() => new Contact(
            agbcode: "01234567",
            name: new Name(
                initials: "J",
                name: "Doe"
            ),
        ),
        fn() => new Organisation(
            name: "HP Doe",
            agbcode: "07654321",
            source: "VEKTIS"
        )
    ], [
        "ORC|NW|ZP12345||ZP12345|||^^^^^R||202511201040|_background||01234567^Doe, J.^J.^^^^^^vektis",
        fn() => new Contact(
            agbcode: "01234567",
            name: new Name(
                initials: "J",
                name: "Doe"
            ),
        ),
        fn() => new Organisation(
            name: "",
            agbcode: null,
            source: null
        )
    ], [
        "ORC|NW|ZP12345||ZP12345|||^^^^^R||20251212113429+0100|^Joe^s||01122339^Str^H.J.^^^^^^VEKTIS|^^^HP Str&09332211^^^^^HP Str||||09332211^HP Str^VEKTIS||||HP Str^^09332211^^^VEKTIS",
        fn() => new Contact(
            agbcode: "01122339",
            name: new Name(
                initials: "H.J.",
                name: "Str"
            ),
        ),
        fn() => new Organisation(
            name: "HP Str",
            agbcode: "09332211",
            source: "VEKTIS"
        )
    ]
]);


