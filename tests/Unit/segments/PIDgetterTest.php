<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('get name', function () {
    $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Doe - Testname&&Testname&&Doe^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^a^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD");
    $msg = $hl7->getMsg(new Msg());
    //name
    expect($msg->patient->name)
        ->own_lastname->toBe('Testname')
        ->initials->toBe("AB")
        ->lastname->toBe("Doe")
        ->prefix->toBe("")
        ->own_prefix->toBe("");

    //strange name
    $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Doe van- de Testname&&&&^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP~^NET^Internet^fake@mail.com||||||||||||||||||Y|NNNLD");
    $msg = $hl7->getMsg(new Msg());
    expect($msg->patient->name)
        ->own_lastname->toBe("Testname")
        ->lastname->toBe("Doe")
        ->own_prefix->toBe("de")
        ->prefix->toBe("van")
        ->and($msg->patient->email)->toBe("fake@mail.com");


});


it('can read pid', function () {
    $hl7 = new Hl7("hier hl7 wat gedebugged moet worden");
    $msg = $hl7->getMsg(new Msg());
    var_dump($msg);
    exit();
})->skip();