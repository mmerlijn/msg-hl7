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
        $hl7 = "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15
PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP
ORC|NW|NT0000000114-001||NT0000000114|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
OBR|1|NT0000000114-001||NIPT ^NIPT |||202303131553|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS
BLG||CH";


        $hl7_new = new Hl7($hl7);

        $hl7_new->segments[$hl7_new->findSegmentKey("OBR")]->setDate(Carbon::now(), 7); //obr kan niet worden gevonden
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
        expect($hl7_new->setDatetimeFormat('YmdHi')->write())
            ->toBe(
                "MSH|^~\&|LabOnline|SALT|LabOnline|LOL_NIPT|202303131553||OML^O21^OML_O21|361288|P|2.5||||||8859/15" . chr(13) .
                "PID|1||23-000058^^^^PI~021622401^^^NLMINBIZA^NNNLD||van Joost&van&Joost^A^A^^^^L||19910101|F|||TEstweg 1&TEstweg&1^a^Oosterhout^^4901CS^NL^M||0612300123^^CP" . chr(13) .
                "ORC|NW|NT0000000114-001||Nummer|||||202303131552|Achtergrond||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
                "OBR|1|NT0000000114-001||NIPT ^NIPT |||" . date('YmdHi') . "|||aschreuder||||||08000158^de Kern^Verloskundigenpraktijk^^^^^^VEKTIS" . chr(13) .
                "SPM|1|SB42412KS||^BCBB^NIPT EDTA|||||||||||||202303131553|||N" . chr(13) .
                "Z03|AZLD" . chr(13) .
                "SPM|2|SB42412KZ||^BCBB^NIPT EDTA|||||||||||||202303131553|||N" . chr(13) .
                "Z03|AZLD" . chr(13)
            );
    }
}