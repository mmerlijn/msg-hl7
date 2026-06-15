<?php

use mmerlijn\msgHl7\Hl7;

it('test me', function () {
    $hl7="MSH|^~\&|ZorgDomein||OrderModule|SALT|20260609162002+0200||OML^O21^OML_O21|ae4ec9f8aa994a20b277|P|2.5.1|||||NLD|8859/1
NTE|1|P|Laboratorium|ZD_CLUSTER_NAME^ZorgDomein clusternaam^L
PID|1||082255945^^^NLMINBIZA^NNNLD~ZD202167085^^^ZorgDomein^VN||de Nijs&de&Nijs^M^^^^^L||19460502|M|||Boetonstraat 38 I&Boetonstraat&38^I^Amsterdam^^1095XN^NL^M||~~^NET^Internet^denijsmaarten@gmail.com||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^L
IN1|1|^null|3311^^^VEKTIS^UZOVI|Zilveren Kruis||||||||||||||||||||||||||||||||971040351
ORC|NW|ZD202167085||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|1||||||||R^Routine^HL70485
OBR|1|ZD202167085||LABEDG001^laboratorium^L|||||||O|||||01100226^Honig^A.D.^^^^^^VEKTIS
ORC|NW|GMZDMMJVHA2DIMA||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|2||||||||R^Routine^HL70485
OBR|2|GMZDMMJVHA2DIMA||MALB^Albumine (micro) urine portie (ACR)^L|||||||O||||UR&Urine&L|01100226^Honig^A.D.^^^^^^VEKTIS
SPM|1|||UR^Urine^L||||||||||||||||N|||||||^Urine (02)^L
ORC|NW|FU2DGNRYG43DIMA||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|3||||||||R^Routine^HL70485
OBR|3|FU2DGNRYG43DIMA||GLUCNU^Glucose nuchter^L|||||||O||||BLD&Bloed&L|01100226^Honig^A.D.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Glucose (04)^L
ORC|NW|GE4DANBQGMZDIMJX||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|4||||||||R^Routine^HL70485
OBR|4|GE4DANBQGMZDIMJX||HBA1C^HbA1c^L|||||||O||||BLD&Bloed&L|01100226^Honig^A.D.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^EDTA (10)^L
ORC|NW|FUZDCOJQGIYTIMJZ||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|5||||||||R^Routine^HL70485
OBR|5|FUZDCOJQGIYTIMJZ||KREA^Kreatinine (bloed) (eGFR)^L|||||||O||||BLD&Bloed&L|01100226^Honig^A.D.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L
ORC|NW|GE4DENRVGQYDGNBU||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|6||||||||R^Routine^HL70485
OBR|6|GE4DENRVGQYDGNBU||K24^Lipidenspectrum (Cholesterol, HDL.Tri,...)^L|||||||O||||BLD&Bloed&L|01100226^Honig^A.D.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L
ORC|NW|GEZTOMZTHA3TENJQ||ZD202167085|||^^^^^R||20260609161813+0200|^Marhour^L.||01100226^Honig^A.D.^^^^^^VEKTIS|^^^^^^^^A.D. Honig||||01059139^A.D. Honig^VEKTIS||||A.D. Honig^^01059139^^^VEKTIS|Insulindeweg 476&Insulindeweg&476^^Amsterdam^^1094MH^NL|0206652055^WPN^PH
TQ1|7||||||||R^Routine^HL70485
OBR|7|GEZTOMZTHA3TENJQ||K7^Natrium/Kalium^L|||||||O||||BLD&Bloed&L|01100226^Honig^A.D.^^^^^^VEKTIS
SPM|1|||BLD^Bloed^L||||||||||||||||N|||||||^Heparinebuis (01)^L
";
    $msgRepo = new \mmerlijn\msgHl7\Hl7($hl7)->getMsg();
    $new = new Hl7()->setMsg($msgRepo)->setUseSegments(['MSH', 'PID', "PV1", "PV2", "IN1", "ORC", "OBR", "OBX"])->write();
    expect($new)->toBe('bla');
});
