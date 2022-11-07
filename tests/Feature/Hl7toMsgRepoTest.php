<?php

namespace mmerlijn\msgHl7\tests\Feature;

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Msg;

it('Parnassia HL7 bericht test', function (string $dataset) {
    $hl7 = new Hl7($dataset);
    $msgRepo = $hl7->getMsg(new Msg());
    expect($msgRepo->order->request_nr)->toBe("PG123456789");
    expect($msgRepo->patient->bsn)->toBe("123456782");
    expect($msgRepo->patient->name->own_lastname)->toBe("Doe");
    expect($msgRepo->patient->dob->format("Y-m-d"))->toBe("1940-01-01");
    expect($msgRepo->patient->phones[0]->number)->toBe("0612341234");
    expect($msgRepo->patient->address)
        ->street->toBe("Straat")
        ->city->toBe("Zaandam")
        ->building->toBe("10")
        ->postcode->toBe("1040AA");
    expect($msgRepo->order->results[0]->test_code)->toBe("Nuchter");
    expect($msgRepo->order->results[0]->value)->toBe("NEE");


})->with([
    "parnassia" => ["MSH|^~\&|CyberLab||LIS||20221008070043||ORM^O01|10921310028203407|P|2.4|12||NE|AL
PID|1||88776655^^^PIN^PT~123456782^^^NLMINBIZA^NNNLD||Doe^^BE^^^^L||19400101|F|^^^^^^A||Straat^10^ZAANDAM^^1040 AA^NL||06-12341234
ORC|NW|PG123456789|||||^^^20221107070000^^R||20221107070000|abcsec|abcsec|ABCSEC|DDOUDPG-P-1500AA120
OBR|1|PG123456789||Thuisprikken|R||20221107070000||||||||^^|ABCSEC|||||||||||^^^^^R
OBR|2|PG123456789||DIF|R||20221107070000||||||||^^|ABCSEC|||||||||||^^^^^R
OBX|1|FT|Nuchter||NEE||||||F"]
]);

it('cancel test', function (string $dataset) {
    $hl7 = new Hl7($dataset);
    $msgRepo = $hl7->getMsg(new Msg());
    expect($msgRepo->order->request_nr)->toBe("PG123456789");
    expect($msgRepo->order->control)->toBe(OrderControlEnum::CANCEL);

})->with([
    "parnassia Cancel" => ["MSH|^~\&|CyberLab||LIS||20201124095356||ORM^O01|88587400212222222|P|2.4|12||NE|AL
ORC|CA|PG123456789|||||^^^20201124070000^^R||20201124070000|abcde|blauw1|AABBSG|DDOUG-P-1900AA4a|||Cancelled in CyberLab."]
]);

