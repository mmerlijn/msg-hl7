<?php

use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Msg;

it('can get PV2', function () {
    $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PV2|||CODE001^lab^99zda");
    $msg = $hl7->getMsg(new Msg());
    expect($msg->order)
        ->admit_reason_name->toBe("lab")
        ->admit_reason_code->toBe("CODE001");
});

it('can set PV2', function () {
    $msg = new Msg();
    $msg->order->admit_reason_code = "ABC123";
    $msg->order->admit_reason_name = "LAB";
    $string = (new Hl7())->setMsg($msg)->write();
    expect($string)->toContain("ABC123^LAB^99zda");
});
