<?php

use mmerlijn\msgHl7\Hl7;


it('test me', function () {
    $hl7="MSH|^~\&|ZorgDomein||OrderModule|SALT|20260609162002+0200||OML^O21^OML_O21|ae4ec9f8aa994a20b277|P|2.5.1|||||NLD|8859/1
NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L
PID|1||082255945^^^NLMINBIZA^NNNLD~ZD202167085^^^ZorgDomein^VN||de Nijs&de&Nijs^M^^^^^L||19460502|M|||Boetonstraat 38 I&Boetonstraat&38^I^Amsterdam^^1095XN^NL^M||~~^NET^Internet^denijsmaarten@gmail.com||||||||||||||||||Y|NNNLD

";
    $hl7O = new \mmerlijn\msgHl7\Hl7($hl7);
    $msgRepo = $hl7O->getMsg();
    //$msgRepo->order->addRequest(new \mmerlijn\msgRepo\Request(test:new \mmerlijn\msgRepo\TestCode(code:"ab",value:"ba")));

    $new = $hl7O->setMsg($msgRepo)->setUseSegments(['MSH', 'PID', "PV1", "PV2", "IN1", "ORC", "OBR", "OBX"])->write();
    expect($new)->toBe($hl7);
});
