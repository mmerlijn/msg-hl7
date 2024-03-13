<?php

namespace mmerlijn\msgHl7\tests\Unit;


use Carbon\Carbon;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\segments\SPM;
use mmerlijn\msgHl7\segments\Z03;

class DumpTest extends \mmerlijn\msgHl7\tests\TestCase
{

    public function test_dump()
    {
        $hl7 = "MSH|^~\&|ZorgDomein||OrderModule||20240313161130||ORM^O01^ORM_O01|6e89124c627f40648978|P|2.4|||||NLD|8859/1
PID|1||323987382^^^NLMINBIZA^NNNLD~ZD694444356^^^ZorgDomein^VN||van der Horst&&van der Horst^K^B^^^^L||19730309|F|||Jdottestraat 288&Jdottestraat&288^^Kerkwijk^^7361TD^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||BEEEDG020^Fundusfoto^99zda
IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789
ORC|NW|ZD694444356||ZD694444356|||^^^^^R||20240313161130|^Doe^J||06659793^Schouten^B^^^^^^VEKTIS|^^^Huisartsenpraktijk Schouten&06659793^^^^^Huisartsenpraktijk Schouten||||06659793^Huisartsenpraktijk Schouten^VEKTIS||||Huisartsenpraktijk Schouten^^06659793^^^VEKTIS
OBR|1|ZD694444356||FUNDUS^Fundusfoto ^99zdl|||||||O|||||06659793^Schouten^R^^^^^^VEKTIS
OBX|1|CE|^Indicatie||HYPERT^Hypertensie^99zda||||||F
OBX|2|ST|DIABM^Diabetes mellitus type I/II sinds^99zda||Sinds 2015||||||F
OBX|3|ST|FKLACHT^Klachten^99zda||Hyper klachten||||||F
OBX|4|ST|RMEDI^Medicatie^99zda||metformine||||||F
OBX|5|ST|FOPM^Opmerking^99zda||Sinds kort gestart met lantus ivm hoog Hba1c\.br\Laatste fundus 2020||||||F
OBR|2|ZD694444356||TIJD^TIJD^99zdl|||||||O|||||06659793^Schouten^B^^^^^^VEKTIS
";
        $hl7 = (new Hl7($hl7))->setDatetimeFormat("YmdHis")->setRepeatORC(false)->filterRequestCode(['TIJD'])->write();
        expect($hl7)->toBe("MSH|^~\&|ZorgDomein||OrderModule||20240313161130||ORM^O01^ORM_O01|6e89124c627f40648978|P|2.4|||||NLD|8859/1" . chr(13) .
            "PID|1||323987382^^^NLMINBIZA^NNNLD~ZD694444356^^^ZorgDomein^VN||van der Horst&&van der Horst^K^B^^^^L||19730309|F|||Jdottestraat 288&Jdottestraat&288^^Kerkwijk^^7361TD^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD" . chr(13) .
            "PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V" . chr(13) .
            "PV2|||BEEEDG020^Fundusfoto^99zda" . chr(13) .
            "IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789" . chr(13) .
            "ORC|NW|ZD694444356||ZD694444356|||^^^^^R||20240313161130|^Doe^J||06659793^Schouten^B^^^^^^VEKTIS|^^^Huisartsenpraktijk Schouten&06659793^^^^^Huisartsenpraktijk Schouten||||06659793^Huisartsenpraktijk Schouten^VEKTIS||||Huisartsenpraktijk Schouten^^06659793^^^VEKTIS" . chr(13) .
            "OBR|1|ZD694444356||FUNDUS^Fundusfoto ^99zdl|||||||O|||||06659793^Schouten^R^^^^^^VEKTIS" . chr(13) .
            "OBX|1|CE|^Indicatie||HYPERT^Hypertensie^99zda||||||F" . chr(13) .
            "OBX|2|ST|DIABM^Diabetes mellitus type I/II sinds^99zda||Sinds 2015||||||F" . chr(13) .
            "OBX|3|ST|FKLACHT^Klachten^99zda||Hyper klachten||||||F" . chr(13) .
            "OBX|4|ST|RMEDI^Medicatie^99zda||metformine||||||F" . chr(13) .
            "OBX|5|ST|FOPM^Opmerking^99zda||Sinds kort gestart met lantus ivm hoog Hba1c\.br\Laatste fundus 2020||||||F" . chr(13)
        );
    }
}