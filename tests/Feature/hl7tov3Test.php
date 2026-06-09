<?php

it('test v2=>v3 conversie', function () {
    $old = "
";
    $old = str_replace(["^99zdl", "^99zda"], "^L", $old);

    $msg = new \mmerlijn\msgHl7\Hl7($old)->getMsg();
    $msg->receiver->facility = "SALT";
    $msg->msgType = new \mmerlijn\msgRepo\MsgType("OML", "O21", "OML_O21", "2.5.1");
    $msg->addComment("Laboratorium"); //    NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L

//$randomCode = new \mmerlijn\msgHl7\helpers\RandomCode(16); //dit is niet nodig volgens de omschrijving
    $spm = new \mmerlijn\msgHl7\helpers\CodeToSPM();
    foreach ($msg->order->requests as $k => $request) {
        //$msg->order->requests[$k]->id=$randomCode->generate();
        $msg->order->requests[$k]->addSpecimen($spm->convert($request->test->code));
    }
    array_splice($msg->order->requests, 0, 0, [new \mmerlijn\msgRepo\Request(test: $msg->order->admit_reason)]);
    $new = new \mmerlijn\msgHl7\Hl7()->setMsg($msg)->useTQ1()->useSPM()
        ->force("P", "NTE", 2)
        ->force("ZD_CLUSTER_NAME", "NTE", 4)
        ->force("ZorgDomein clusternaam", "NTE", 4, 0, 1)
        ->force("L", "NTE", 4, 0, 2)
        ->write();
    expect($new)->toBe("");
});
