<?php

namespace mmerlijn\msgHl7\tests\Unit;


use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\tests\TestCase;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Request;


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
OBX|2|CE|COVIDURG^Urgentie?^99zdl||true^Urgent (vandaag best effort NIET CITO)^99zda||||||F
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


        //remove line below
        //$this->assertStringContainsString("||20220103000|", $string);
    }
}