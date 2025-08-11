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

it('can read this hl7', function () {
    $msg = "MSH|^~\&|ZorgDomein||OrderModule||20240417172619+0200||ORM^O01^ORM_O01|60d2b5bb2af84273941a|P|2.4|||||NLD|8859/1
PID|1||131254625^^^NLMINBIZA^NNNLD~ZD146489882^^^ZorgDomein^VN||Dieterman&&Dieterman^I^^^^^L||19660320|F|||Schoenerstraat 36&Schoenerstraat&36^^Amsterdam^^1034XG^NL^M||0624437534^ORN^CP~0651601491^PRN^PH||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||BEEEDG023^Longfunctieonderzoek^99zda
IN1|1|^null|0^^^LOCAL|ONVZ Ziektekostenverzekeraar||||||||||||||||||||||||||||||||2173924200
ORC|NW|ZD146489882||ZD146489882|||^^^^^R||20240417172211+0200|01101389^van Essen - Rubingh^A.A.^^^^^^VEKTIS||01101389^van Essen - Rubingh^A.A.^^^^^^VEKTIS|^^^Huisartsenpraktijk Antarus&01008080^^^^^Huisartsenpraktijk Antarus||||01008080^Huisartsenpraktijk Antarus^VEKTIS||||Huisartsenpraktijk Antarus^^01008080^^^VEKTIS
OBR|1|ZD146489882||SPIROM^Spirometrie Diagnostiek^99zdl|||||||O|||||01101389^van Essen - Rubingh^A.A.^^^^^^VEKTIS
OBX|1|ST|REDE^Reden van aanvraag^99zda||onbegrepen benauwdheid, pijn links thoracaal en in de ochtend een piepende ademhaling. Niet bekend met atsma of allergie\.br\heeft long covid.\.br\Ook psychosociale problematiek wat mgl van invloed is||||||F
OBX|2|ST|ANAM^Anamnese^99zda||15-04-2024, okt 23 echo bovenbuik goed, april 24 x-th gb, lab jan goed.  Heeft in de ochtend een piepende ademhaling. Bij traplopen kortademig. Heeft pijn bovenbuik.||||||F
OBX|3|CE|ZWAN^Zwangerschap^99zda||n^Nee^99zda||||||F
OBX|4|CE|KLINGEG^Relevante klinische gegevens^99zda||rkr^Regelmatig kortademigheid in rust^99zda~rki^Regelmatig kortademigheid bij inspanning^99zda~ak^Aanvalsgewijze kortademigheid^99zda~rm^Regelmatig maagklachten^99zda||||||F
OBX|5|CE|ALLE^Allergie^99zda||n^Nee^99zda||||||F
OBX|6|CE|PBIZ^Patiënt bekend in ziekenhuis^99zda||j^Ja. Geef indien van toepassing ook aan bij welke afdeling/specialisme en welke locatie, gynaecologie BovenIJ, interne BovenIJ^99zda||||||F
OBX|7|CE|ROOKGEDRAG^Rookgedrag^99zda||n^Nooit^99zda||||||F
OBX|8|CE|^Diagnose||PBMANDE^Anders:, onbekend^99zda||||||F
OBX|9|CE|^Reden van verwijzen||ONBDYS^Onbegrepen dyspnoe^99zda||||||F
OBX|10|CE|^Relevante co-morbiditeit||MOROVER^Overig, geen^99zda||||||F
OBX|11|CE|^Overige relevante medicatie||RMEDI^Relevante medicatie, pantozol, levothyroxine^99zda||||||F
OBX|12|CE|^Exacerbaties in afgelopen jaar||EXAC^Aantal:, 0^99zda||||||F
OBX|13|CE|^Contra indicatie voor salbutamol||CONTNEE^Nee^99zda||||||F
ORC|NW|ZD146489882||ZD146489882|||^^^^^R||20240417172211+0200|01101389^van Essen - Rubingh^A.A.^^^^^^VEKTIS||01101389^van Essen - Rubingh^A.A.^^^^^^VEKTIS|^^^Huisartsenpraktijk Antarus&01008080^^^^^Huisartsenpraktijk Antarus||||01008080^Huisartsenpraktijk Antarus^VEKTIS||||Huisartsenpraktijk Antarus^^01008080^^^VEKTIS
OBR|2|ZD146489882||TIJD^TIJD^99zdl|||||||O|||||01101389^van Essen - Rubingh^A.A.^^^^^^VEKTIS
";
    $hl7 = new Hl7($msg);
    $msgRepo = $hl7->getMsg(new Msg());
    expect($msgRepo->order->request_nr)->toBe("ZD146489882");
});

