<?php

namespace mmerlijn\msgHl7\tests\Unit;


use Carbon\Carbon;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\segments\BLG;
use mmerlijn\msgHl7\segments\SPM;
use mmerlijn\msgHl7\segments\Z03;
use mmerlijn\msgHl7\tests\TestCase;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;


class Hl7Test extends TestCase
{
    public function test_segment_splitter()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD");

        var_dump(($hl7->getMsg(new Msg())));
    }

    public function test_read_hl7_line()
    {
        $hl7 = new Hl7("");
        var_dump(($hl7->getMsg(new Msg()))->toArray());
    }

    public function test_read_modify_and_add_request()
    {
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
        $string = $hl7->write(true);
        $this->assertStringContainsString("ABC^A_B_C^99zdl", $string);
        $this->assertStringContainsString("|^Doe^J||01123456^van der Plas", $string);
        $this->assertStringContainsString("||20220102103000|", $string);

        var_dump($msg->toArray());
        //remove line below
        //$this->assertStringContainsString("||20220103000|", $string);
    }


    public function test_orm_example()
    {
        $hl7 = "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15
PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP
ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
BLG||CH";


        $hl7_new = new Hl7($hl7);

        $hl7_new->segments[$hl7_new->findSegmentKey("OBR")]->setData(date("YmdHi"), 7); //obr kan niet worden gevonden
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
        $this->assertSame($hl7_new->write(), "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15" . chr(13) .
            "PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP" . chr(13) .
            "ORC|NW|NT0000000114-001||Nummer|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
            "OBR|1|NT0000000114-001||NIPT ^NIPT |||" . date('YmdHi') . "|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
            "SPM|1|SB42412KS||^BCBB^NIPT EDTA|||||||||||||202303131553|||N" . chr(13) .
            "Z03|AZLD" . chr(13) .
            "SPM|2|SB42412KZ||^BCBB^NIPT EDTA|||||||||||||202303131553|||N" . chr(13) .
            "Z03|AZLD" . chr(13));
        //var_dump($hl7_new->write());
        //die();
    }

    public function test_remove_segment()
    {
        $hl7 = "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15
PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP
ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
TQ1|||||||||R
OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
BLG||CH";
        $hl7_new = new Hl7($hl7);
        $hl7_new->removeSegment("TQ1");
        $hl7_new->removeSegment("BLG");
        $this->assertEquals($hl7_new->write(),
            "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15" . chr(13) .
            "PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP" . chr(13) .
            "ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
            "OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13)
        );
    }

    public function test_specific_request_with_own_results()
    {
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
        $this->assertSame('Hallo', $string);
    }
}
