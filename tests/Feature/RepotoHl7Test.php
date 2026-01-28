<?php

namespace mmerlijn\msgHl7\tests\Feature;

use Carbon\Carbon;
use Exception;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgHl7\segments\Z03;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Contact;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Enums\OrderWhereEnum;
use mmerlijn\msgRepo\Enums\PatientSexEnum;
use mmerlijn\msgRepo\Enums\ValueTypeEnum;
use mmerlijn\msgRepo\Id;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Observation;
use mmerlijn\msgRepo\Patient;
use mmerlijn\msgRepo\Phone;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;
use mmerlijn\msgRepo\TestCode;


it('create hl7', function () {
    $repo = new Msg();

    $repo->sender->application = "agendasalt";
    $repo->sender->facility = "SALT";
    $repo->receiver->application = "Mirth";
    $repo->receiver->facility = "Test";
    $repo->datetime = Carbon::create("2023-12-11T11:00:00"); //default current datetime
    $repo->security_id = ""; //optional
    $repo->msgType->type = "ORM";
    $repo->msgType->trigger = "001";
    $repo->msgType->structure = "ORM_001";
    $repo->id = 123; //unique message id
    $repo->msgType->version = "2.5"; //default
    $repo->order->request_nr = "AB123";
    $repo->order->admit_reason->value = "laboratorium";
    $repo->order->admit_reason->code = "LABEDG001";
    $repo->order->admit_reason->source = "99zda";
    $repo->order->addRequest(new Request(test: new TestCode(code: "TST", value: "Testname", source: "SRC")));
    $repo->order->addObservation(new Observation(type: ValueTypeEnum::ST, value: 123, test: new TestCode(code: "TST", value: "Testname", source: "SRC")));
    $repo->order->requester = new Contact(
        agbcode: '12345678',
        name: new Name(initials: 'A', own_lastname: 'Groot', own_prefix: 'de'),
        source: 'VEKTIS',
        address: new Address(postcode: '1000CC', city: 'Amsterdam', street: 'Schoonstraat', building: '38a'), phone: new Phone(number: '0612345678')
    );
    $repo->order->request_at = Carbon::create("2023-12-11T11:00:00");
    $repo->setPatient(new Patient(
        sex: PatientSexEnum::MALE,
        name: new Name(initials: 'A', own_lastname: 'Groot', own_prefix: 'de'),
        dob: "2000-01-01",
        bsn: '123456782',
        address: new Address(postcode: '1000CC', city: 'Amsterdam', street: 'Schoonstraat', building: '38a'),
        phones: [new Phone(number: '0612345678')],
        insurance: new Insurance(uzovi: '123', policy_nr: '123456789'),
        last_requester: "Groot, A. de",
        email: 'test@mail.com'
    ));
    $repo->patient->addId(new Id(id: '1234', authority: 'ZorgDomein', code: 'VN'));
    $repo->order->control = OrderControlEnum::NEW;
    $repo->order->priority = false;
    $repo->addSegment("PV1.4.0.1", "Test");
    $hl7 = new Hl7();
    try {
        $hl7->setDatetimeFormat("YmdHis")
            ->setRepeatORC()
            ->setMsg($repo)
            ->addSegment((new Z03())->setData( 'ABC', 1))
            ->setUseSegments(["MSH", "PID", "PV1", "PV2", "IN1", "ORC", "OBR", "OBX","Z03"]);
    } catch (Exception $e) {
        dd($e);
    }
    //expect($hl7->segments[5]->data[7][0][0][0])->toBeInstanceOf(Carbon::class);
    $out = $hl7->setDatetimeFormat("YmdHis")->write();
    expect($out)
        ->toBe('MSH|^~\&|agendasalt|SALT|Mirth|Test|20231211110000||ORM^001^ORM_001|123|P|2.5|||||NLD|8859/1' . chr(13) .
            'PID|1||123456782^^^NLMINBIZA^NNNLD~1234^^^ZorgDomein^VN||de Groot&de&Groot^A^^^^^L||20000101|M|||Schoonstraat 38 a&Schoonstraat&38^a^Amsterdam^^1000CC^NL^M||06 1234 5678^PRN^CP~^NET^Internet^test@mail.com||||||||||||||||||Y|NNNLD' . chr(13) .
            'PV1|1|O||^Test|||||||||||||||||||||||||||||||||||||||||||||||V' . chr(13) .
            'PV2|||LABEDG001^laboratorium^99zda' . chr(13) .
            'IN1|1|^null|123^^^VEKTIS^UZOVI|||||||||||||||||||||||||||||||||123456789' . chr(13) .
            'ORC|NW|AB123||AB123|||^^^^^R||20231211110000|||12345678^de Groot^A^^^^^^VEKTIS' . chr(13) .
            'OBR|1|AB123||TST^Testname^SRC|R|||||||||||12345678^de Groot^A^^^^^^VEKTIS' . chr(13) .
            'OBX|1|ST|TST^Testname^SRC||123||||||F' . chr(13) .
            'Z03|ABC' . chr(13));


});


it('can create msg', function () {

    $msg = new Msg();
    $msg->setPatient(new Patient(
        sex: PatientSexEnum::FEMALE, name: new Name(initials: "A", own_lastname: "Klass"), dob: Carbon::now()->subYears(20), bsn: "123456782", address: new Address(postcode: "8000AA", city: 'Adam', street: "straat", building: "30a"), phones: [new Phone("0612121212")]
    ));
    $msg->patient->addId(
        new Id(
            id: "ZD12345678", authority: "ZorgDomein", code: "VN"
        )
    )->addId(
        new Id(
            id: 45, authority: "SALTNET", code: "VN"
        )
    );
    $msg->sender = new Contact(
        application: 'SALTNET',
        facility: "LBSPATIENTNR"
    );
    $msg->order->requester = new Contact(
        agbcode: "01123456",
        name: new Name(initials: "A.", own_lastname: "Testarts"), source: "VEKTIS"
    );
    $msg->order->entered_by = new Contact(
        agbcode: "01123456",
        name: new Name(initials: "A.", own_lastname: "Testarts"), source: "VEKTIS"
    );
    $msg->order->request_nr = 'FK' . (100000000 + 45);
    $msg->order->where = OrderWhereEnum::EMPTY;
    $msg->order->priority=true;
    $msg->order->addRequest(
        new Request(
            test: new TestCode(code: "DUMMY", value: "DUMMY", source: "L")
        )
    );
    $hl7 = (new Hl7())->setMsg($msg)->setDatetimeFormat("YmdHis")->setUseSegments(['MSH', 'PID', 'PV1', 'IN1', 'ORC', 'OBR', 'OBX']);
    dd($hl7->write());
})->skip();

it('can read hl7, set it to msg and write hl7',function(){
    $hl7string= "MSH|^~\&|ZorgDomein||OrderModule||20251215215057+0100||ORM^O01^ORM_O01|c3c8f363-1b66-329e-88d6-969eab7fe721|P|2.4|||||NLD|8859/1
PID|1||ZD248026420^^^ZorgDomein^VN||Molenaar&&Molenaar^J^B^^^^L||19490129|F|||Van Wijkstraat 92-68&Van Wijkstraat&92^68^Kornhorn^^9801TA^NL^M||020 0536 242^PRN^PH||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^99zda
IN1|1|^null|3343^^^VEKTIS^UZOVI
ORC|NW|AB123||AB123|||^^^^^R||20251215215057+0100|||12345678^de Groot^A^^^^^^VEKTIS
TQ1|1||||||||R^Routine^HL70485
OBR|1|ZD248026420||GLUCNU^Glucose nuchter^99zda|R||||||O|||||12345678^de Groot^A^^^^^^VEKTIS
ORC|NW|AB123||AB123|||^^^^^R||20251215215057+0100|||12345678^de Groot^A^^^^^^VEKTIS
TQ1|2||||||||R^Routine^HL70485
OBR|2|ZD248026420||ALAT^ALAT^99zda|R||||||O|||||12345678^de Groot^A^^^^^^VEKTIS
OBX|1|ST|AI^Opmerkingen / klinische gegevens^99zdl||Ischemische colitis, kwetsbare gezondheid||||||F";
    $hl7=new Hl7($hl7string);
    $msg=$hl7->getMsg(new Msg());
    $outHl7=(new Hl7())->setMsg($msg)->setUseSegments(['MSH', 'PID', 'PV1', 'PV2', 'IN1', 'ORC', 'OBR', 'OBX'])->write();
    expect($outHl7)->toBe("MSH|^~\&|ZorgDomein||OrderModule||20251215215057+0100||ORM^O01^ORM_O01|c3c8f363-1b66-329e-88d6-969eab7fe721|P|2.4|||||NLD|8859/1".chr(13).
"PID|1||ZD248026420^^^ZorgDomein^VN||Molenaar&&Molenaar^J^B^^^^L||19490129|F|||Van Wijkstraat 92-68&Van Wijkstraat&92^68^Kornhorn^^9801TA^NL^M||020 0536 242^PRN^PH||||||||||||||||||Y|NNNLD".chr(13).
"PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V".chr(13).
"PV2|||LABEDG001^laboratorium^99zda".chr(13).
"IN1|1|^null|3343^^^VEKTIS^UZOVI".chr(13).
"ORC|NW|AB123||AB123|||^^^^^R||20251215215057+0100|||12345678^de Groot^A^^^^^^VEKTIS".chr(13).
"OBR|1|ZD248026420||GLUCNU^Glucose nuchter^99zda|R||||||O|||||12345678^de Groot^A^^^^^^VEKTIS".chr(13).
"ORC|NW|AB123||AB123|||^^^^^R||20251215215057+0100|||12345678^de Groot^A^^^^^^VEKTIS".chr(13).
"OBR|2|ZD248026420||ALAT^ALAT^99zda|R||||||O|||||12345678^de Groot^A^^^^^^VEKTIS".chr(13).
"OBX|1|ST|AI^Opmerkingen / klinische gegevens^99zdl||Ischemische colitis, kwetsbare gezondheid||||||F".chr(13));
});
