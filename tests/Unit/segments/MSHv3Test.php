<?php

it('can read message details', function () {
    $hl7v3msg = new \mmerlijn\msgHl7\Hl7("MSH|^~\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1");
    $msg = $hl7v3msg->getMsg(new \mmerlijn\msgRepo\Msg());
    expect($msg->sender->application)->toBe("ZorgDomein")
        ->and($msg->receiver->application)->toBe("Labtrain")
        ->and($msg->datetime->format('YmdHisO'))->toBe("20251112125133+0100")
        ->and($msg->msgType->type)->toBe("OML")
        ->and($msg->msgType->trigger)->toBe("O21")
        ->and($msg->msgType->structure)->toBe("OML_O21")
        ->and($msg->id)->toBe("d0a06274854e4824a8a4")
        ->and($msg->processing_id)->toBe("P")
        ->and($msg->msgType->version)->toBe("2.5.1");

});

it('can write message details', function () {
    $msg = new \mmerlijn\msgRepo\Msg(
        sender: ['application' => 'ZorgDomein'],
        receiver: ['application' => 'Labtrain', 'facility' => 'SALT'],
        datetime: '2025-11-12 12:51:33+01:00',
        msgType: new \mmerlijn\msgRepo\MsgType(
            type: 'OML',
            trigger: 'O21',
            structure: 'OML_O21',
            version: '2.5.1',
            charset: '8859/1',
        ),
        id: 'd0a06274854e4824a8a4',
        processing_id: 'P',
    );
    $msh = new \mmerlijn\msgHl7\segments\MSH();
    $msh->setMsg($msg);
    $hl7line = $msh->write();
    expect($hl7line)->toBe("MSH|^~\\&|ZorgDomein||Labtrain|SALT|20251112125133+0100||OML^O21^OML_O21|d0a06274854e4824a8a4|P|2.5.1|||||NLD|8859/1");
});
