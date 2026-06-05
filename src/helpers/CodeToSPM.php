<?php

namespace mmerlijn\msgHl7\helpers;

use mmerlijn\msgRepo\Specimen;
use mmerlijn\msgRepo\TestCode;
use PHPUnit\Framework\Attributes\Test;

class CodeToSPM
{
    public function __construct(){

    }
    public function convert(string $code): Specimen
    {
        return match($code) {

            "GLUCNN" =>
            new Specimen(type:new TestCode("BLD", "Bloed", "L"),container:new TestCode("", "Glucose (04)", "L")),
            "CHOL","CHOL_HDL","KREA","LDL","K7","NHDLP","TRIG","ALAT","ASAT","AF","AMY_LIP","CRP","FERR","GGT","FE","TSHFT4","FOLZ" =>
            new Specimen(type:new TestCode("BLD", "Bloed", "L"),container: new TestCode("", "Heparinebuis (01)", "L")),
            "MALB" => new Specimen(type:new TestCode("UR","Urine","L"),container: new TestCode("","Urine (02)","L")),
            "BSE","VKB" =>new Specimen(type:new TestCode("BLD", "Bloed", "L"),container: new TestCode("", "EDTA (11)", "L")),
            "COELIAKI"=>new Specimen(type:new TestCode("BLD", "Bloed", "L"),container: new TestCode("", "Stolbuis (25)", "L")),
            "TSHSCR"=>new Specimen(type:new TestCode("BLD", "Bloed", "L"),container: new TestCode("", "Heparinebuis (01) EDTA (11)", "L")),

            default => new Specimen(type:new TestCode("XXXXXXXXX{$code}XXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXX{$code}XXXXXXXXXXXXXXXXXX", "L"))
        };



    }
}