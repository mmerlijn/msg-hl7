<?php

namespace mmerlijn\msgHl7\tests\Unit;


use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\tests\TestCase;
use mmerlijn\msgRepo\Msg;


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
}