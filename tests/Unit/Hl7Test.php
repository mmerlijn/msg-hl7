<?php


use Carbon\Carbon;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\segments\OBR;
use mmerlijn\msgHl7\segments\ORC;
use mmerlijn\msgHl7\segments\SPM;
use mmerlijn\msgHl7\segments\Z03;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;

it('can split segements', function () {
    $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD");

    expect($hl7->segments[0])
        ->toBeInstanceOf(\mmerlijn\msgHl7\segments\MSH::class)
        ->and($hl7->segments[0]->data[3][0][0][0])->toBe('ZorgDomein')
        ->and($hl7->segments[0]->data[5][0][0][0])->toBe('OrderModule')
        ->and($hl7->segments[0]->data[7][0][0][0])->toBeInstanceOf(Carbon::class)
        ->and($hl7->segments[1])
        ->toBeInstanceOf(\mmerlijn\msgHl7\segments\PID::class)
        ->and($hl7->segments[1]->data[1][0][0][0])->toBe("1")
        ->and($hl7->segments[1]->data[3][0][0][0])->toBe('123456782')
        ->and($hl7->segments[1]->data[3][1][0][0])->toBe('ZD12345678')
        ->and($hl7->segments[1]->data[7][0][0][0])->toBeInstanceOf(Carbon::class);
})->group('hl7');

it('can read HL7 line', function () {
    $hl7 = new Hl7("");
    var_dump(($hl7->getMsg(new Msg()))->toArray());
})->group('hl7')->skip();

it('can be modified', function () {
    $hl7 = "MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^99zda
IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|1|ZD12345678||CRP^CRP^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
OBX|1|ST|COVIDSYM^Covid-19 verdacht^99zdl||false||||||F
OBX|2|CE|COVIDURG^Urgentie?^99zdl||6 mnd^Binnen 6 mnd^99zda||||||F
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|2|ZD12345678||TIJD^TIJD^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
";
    $hl7 = new Hl7($hl7);
    $msg = $hl7
        ->setDatetimeFormat("YmdHis")
        ->setRepeatORC()
        ->getMsg(new Msg());
    $msg->order->addRequest(new Request(test_code: "ABC", test_name: "A_B_C"));
    $hl7->setMsg($msg);
    expect($hl7->datetime_format)->toBe("YmdHis");
    expect($hl7->segments[0]->data[7][0][0][0])->toBeInstanceOf(Carbon::class);
    $string = $hl7->write(true);
    expect($string)->toContain("ABC^A_B_C^99zdl")
        ->toContain("|^Doe^J||01123456^van der Plas")
        ->toContain("||20220102103000|");

})->group('hl7');

it('can deal with ORM', function () {
    $hl7 = "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15
PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP
ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
BLG||CH";


    $hl7_new = new Hl7($hl7);

    $hl7_new->segments[$hl7_new->findSegmentKey("OBR")]->setDate(Carbon::now(), 7); //obr kan niet worden gevonden
    $hl7_new->addSegment((new SPM())
        ->setData(1, 1)
        ->setData("SB42412KS", 2)
        ->setData("BCBB", 4, 0, 1)
        ->setData("NIPT EDTA", 4, 0, 2)
        ->setData("202303131553", 17)
        ->setData("N", 20)
    );
    $hl7_new->addSegment((new Z03())->setData("AZLD", 1));
    $hl7_new->addSegment((new SPM())
        ->setData(2, 1)
        ->setData("SB42412KZ", 2)
        ->setData("BCBB", 4, 0, 1)
        ->setData("NIPT EDTA", 4, 0, 2)
        ->setData("202303131553", 17)
        ->setData("N", 20)
    );
    $hl7_new->segments[$hl7_new->findSegmentKey("ORC")]->setData("Nummer", 4);
    $hl7_new->addSegment((new Z03())->setData("AZLD", 1));
    $hl7_new->removeSegment("BLG");
    //var_dump($hl7_new->segments);
    expect($hl7_new->setDatetimeFormat('YmdHi')->write())
        ->toBe(
            "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15" . chr(13) .
            "PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP" . chr(13) .
            "ORC|NW|NT0000000114-001||Nummer|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
            "OBR|1|NT0000000114-001||NIPT ^NIPT |||" . date('YmdHi') . "|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
            "SPM|1|SB42412KS||^BCBB^NIPT EDTA|||||||||||||202303131553|||N" . chr(13) .
            "Z03|AZLD" . chr(13) .
            "SPM|2|SB42412KZ||^BCBB^NIPT EDTA|||||||||||||202303131553|||N" . chr(13) .
            "Z03|AZLD" . chr(13)
        );

})->group('hl7');

it('can remove segments', function () {
    $hl7 = "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15
PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP
ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
TQ1|||||||||R
OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
BLG||CH";

    $hl7_new = new Hl7($hl7);
    $hl7_new->removeSegment("TQ1");
    $hl7_new->removeSegment("BLG");
    expect($hl7_new->setDatetimeFormat("YmdHi")->write())->toBe(
        "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15" . chr(13) .
        "PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP" . chr(13) .
        "ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
        "OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13)
    );
});

it('can have results added', function () {
    $hl7 = "MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^99zda
IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|1|ZD12345678||CRP^CRP^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
OBX|1|ST|COVIDSYM^Covid-19 verdacht^99zdl||false||||||F
OBX|2|CE|COVIDURG^Urgentie?^99zdl||6 mnd^Binnen 6 mnd^99zda||||||F
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|2|ZD12345678||TIJD^TIJD^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
";
    $hl7 = new Hl7($hl7);
    $msg = $hl7->getMsg(new Msg());
    $msg->order->addRequest(new Request(test_code: "PRODUCT", test_name: "PRODUCT"));
    $msg->order->addResult(new Result(value: "1", test_code: "GROEN", test_name: "is groen", only_for_request_test_code: 'PRODUCT'));
    $msg->order->addResult(new Result(value: "2", test_code: "ROOD", test_name: "is rood", only_for_request_test_code: 'PRODUCT'));
    $msg->order->addResult(new Result(value: "3", test_code: "BLAUW", test_name: "is blauw"));
    $hl7->setMsg($msg);
    $string = $hl7->write(true);
    expect($string)->toBe("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1" . chr(13) .
        "PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 A&Schoonstraat&38^A^Amsterdam^^1040AB^NL^M||06 1234 1234^PRN^CP||||||||||||||||||Y|NNNLD" . chr(13) .
        "PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V" . chr(13) .
        "PV2|||LABEDG001^laboratorium^99zda" . chr(13) .
        "IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789" . chr(13) .
        "ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS" . chr(13) .
        "OBR|1|ZD12345678||CRP^CRP^99zdl|R||||||O|||||01123456^van der Plas^B^^^^^^VEKTIS" . chr(13) .
        "OBX|1|ST|COVIDSYM^Covid-19 verdacht^99zdl||false||||||F" . chr(13) .
        "OBX|2|CE|COVIDURG^Urgentie?^99zdl||6 mnd^Binnen 6 mnd^99zda||||||F" . chr(13) .
        "OBX|3|NM|BLAUW^is blauw^99zdl||3||||||F" . chr(13) .
        "OBR|2|ZD12345678||TIJD^TIJD^99zdl|R||||||O|||||01123456^van der Plas^B^^^^^^VEKTIS" . chr(13) .
        "OBX|1|ST|COVIDSYM^Covid-19 verdacht^99zdl||false||||||F" . chr(13) .
        "OBX|2|CE|COVIDURG^Urgentie?^99zdl||6 mnd^Binnen 6 mnd^99zda||||||F" . chr(13) .
        "OBX|3|NM|BLAUW^is blauw^99zdl||3||||||F" . chr(13) .
        "OBR|3|ZD12345678||PRODUCT^PRODUCT^99zdl|R||||||O|||||01123456^van der Plas^B^^^^^^VEKTIS" . chr(13) .
        "OBX|1|NM|GROEN^is groen^99zdl||1||||||F" . chr(13) .
        "OBX|2|NM|ROOD^is rood^99zdl||2||||||F" . chr(13));
});

it('can compact HL7', function () {
    $hl7 = "MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^99zda
IN1|1|^null|0^^^LOCAL|Aevitae (EE)||||||||||||||||||||||||||||||||300826427
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|1|ZD12345678||MALB^Albumine (micro) urine portie (ACR)^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|2|ZD12345678||GLUCNN^Glucose^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|3|ZD12345678||KREA^Kreatinine (bloed) (eGFR)^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|4|ZD12345678||K24^Lipidenspectrum (Cholesterol, HDL.Tri,...)^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|5|ZD12345678||K7^Natrium/Kalium^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS";

    $hl7_compact = "MSH|^~\&|ZorgDomein||OrderModule||20220102161545||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1" . chr(13) .
        "PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD" . chr(13) .
        "PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V" . chr(13) .
        "PV2|||LABEDG001^laboratorium^99zda" . chr(13) .
        "IN1|1|^null|0^^^LOCAL|Aevitae (EE)||||||||||||||||||||||||||||||||300826427" . chr(13) .
        "ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS" . chr(13) .
        "OBR|1|ZD12345678||MALB^Albumine (micro) urine portie (ACR)^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS" . chr(13) .
        "OBR|2|ZD12345678||GLUCNN^Glucose^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS" . chr(13) .
        "OBR|3|ZD12345678||KREA^Kreatinine (bloed) (eGFR)^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS" . chr(13) .
        "OBR|4|ZD12345678||K24^Lipidenspectrum (Cholesterol, HDL.Tri,...)^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS" . chr(13) .
        "OBR|5|ZD12345678||K7^Natrium/Kalium^99zdl|||||||O|||||01103660^Mahler^CW^^^^^^VEKTIS" . chr(13);
    $h = new Hl7($hl7);
    $h->setRepeatORC(false);
    $h->setDatetimeFormat("YmdHis");
    $this->assertSame($hl7_compact, $h->write(false));
});

it('can filter testcodes', function () {
    $hl7 = "MSH|^~\&|ZorgDomein||OrderModule||20220102161545||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||BEEEDG020^Fundusfoto^99zda
IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|1|ZD12345678||FUNDUS^Fundusfoto ^99zdl|||||||O|||||06659793^Schouten^R^^^^^^VEKTIS
OBX|1|CE|^Indicatie||HYPERT^Hypertensie^99zda||||||F
OBX|2|ST|DIABM^Diabetes mellitus type I/II sinds^99zda||Sinds 2015||||||F
OBX|3|ST|FKLACHT^Klachten^99zda||Hyper klachten||||||F
OBX|4|ST|RMEDI^Medicatie^99zda||metformine||||||F
OBX|5|ST|FOPM^Opmerking^99zda||Sinds kort gestart met lantus ivm hoog Hba1c\.br\Laatste fundus 2020||||||F
OBR|2|ZD694444356||TIJD^TIJD^99zdl|||||||O|||||06659793^Schouten^B^^^^^^VEKTIS
";
    $hl7 = (new Hl7($hl7))->setDatetimeFormat("YmdHis")->setRepeatORC(false)->filterRequestCode(['TIJD', 'FKLACHT'])->write();
    expect($hl7)->toBe("MSH|^~\&|ZorgDomein||OrderModule||20220102161545||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1" . chr(13) .
        "PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD" . chr(13) .
        "PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V" . chr(13) .
        "PV2|||BEEEDG020^Fundusfoto^99zda" . chr(13) .
        "IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789" . chr(13) .
        "ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS" . chr(13) .
        "OBR|1|ZD12345678||FUNDUS^Fundusfoto ^99zdl|||||||O|||||06659793^Schouten^R^^^^^^VEKTIS" . chr(13) .
        "OBX|1|CE|^Indicatie||HYPERT^Hypertensie^99zda||||||F" . chr(13) .
        "OBX|2|ST|DIABM^Diabetes mellitus type I/II sinds^99zda||Sinds 2015||||||F" . chr(13) .
        "OBX|3|ST|FKLACHT^Klachten^99zda||Hyper klachten||||||F" . chr(13) .
        "OBX|4|ST|RMEDI^Medicatie^99zda||metformine||||||F" . chr(13) .
        "OBX|5|ST|FOPM^Opmerking^99zda||Sinds kort gestart met lantus ivm hoog Hba1c\.br\Laatste fundus 2020||||||F" . chr(13)
    );
    $hl7 = (new Hl7($hl7))->setDatetimeFormat("YmdHisO")->setRepeatORC(false)->filterRequestCode(['TIJD'])->write();
    expect($hl7)->toBe("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0100||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1" . chr(13) .
        "PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD" . chr(13) .
        "PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V" . chr(13) .
        "PV2|||BEEEDG020^Fundusfoto^99zda" . chr(13) .
        "IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789" . chr(13) .
        "ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0100|^Doe^J||01123456^van der Plas^B^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS" . chr(13) .
        "OBR|1|ZD12345678||FUNDUS^Fundusfoto ^99zdl|||||||O|||||06659793^Schouten^R^^^^^^VEKTIS" . chr(13) .
        "OBX|1|CE|^Indicatie||HYPERT^Hypertensie^99zda||||||F" . chr(13) .
        "OBX|2|ST|DIABM^Diabetes mellitus type I/II sinds^99zda||Sinds 2015||||||F" . chr(13) .
        "OBX|3|ST|FKLACHT^Klachten^99zda||Hyper klachten||||||F" . chr(13) .
        "OBX|4|ST|RMEDI^Medicatie^99zda||metformine||||||F" . chr(13) .
        "OBX|5|ST|FOPM^Opmerking^99zda||Sinds kort gestart met lantus ivm hoog Hba1c\.br\Laatste fundus 2020||||||F" . chr(13)
    );
});

it('filters dubbele berichten', function () {
    $hl7 = "MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^99zda
IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|1|ZD12345678||CRP^CRP^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
OBX|1|ST|COVIDSYM^Covid-19 verdacht^99zdl||false||||||F
OBX|2|CE|COVIDURG^Urgentie?^99zdl||6 mnd^Binnen 6 mnd^99zda||||||F
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|2|ZD12345678||TIJD^TIJD^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
";
    $h = new Hl7($hl7 . $hl7);
    expect($h->segments)->toHaveCount(11)
        ->and($h->segments[1])->toBeInstanceOf(\mmerlijn\msgHl7\segments\PID::class)
        ->and($h->segments[9])->toBeInstanceOf(ORC::class)
        ->and($h->segments[10])->toBeInstanceOf(OBR::class);
    //->and($h->setRepeatORC(false)->write())->toBe($hl7);
});