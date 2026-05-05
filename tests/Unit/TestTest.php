<?php

it('', function () {
    $hl7="";
    $msgRepo = new \mmerlijn\msgHl7\Hl7($hl7)->getMsg();
    dd($msgRepo->order->getAllObservations([],true));
});
