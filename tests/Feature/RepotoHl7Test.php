<?php

namespace mmerlijn\msgHl7\tests\Feature;

use Carbon\Carbon;
use Exception;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Contact;
use mmerlijn\msgRepo\Enums\OrderControlEnum;
use mmerlijn\msgRepo\Enums\PatientSexEnum;
use mmerlijn\msgRepo\Id;
use mmerlijn\msgRepo\Insurance;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Patient;
use mmerlijn\msgRepo\Phone;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;


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
    $repo->order->admit_reason_name = "laboratorium";
    $repo->order->admit_reason_code = "LABEDG001";
    $repo->order->addRequest(new Request(test_code: "TST", test_name: "Testname", test_source: "SRC"));
    $repo->order->addResult(new Result(type_of_value: 'ST', value: 123, test_code: "TST", test_name: "Testname", test_source: "SRC"));
    $repo->order->requester = new Contact(
        agbcode: '12345678',
        name: new Name(initials: 'A', own_lastname: 'Groot', own_prefix: 'de'),
        source: 'VEKTIS',
        address: new Address(postcode: '1000CC', city: 'Amsterdam', street: 'Schoonstraat', building: '38a'), phone: new Phone(number: '0612345678')
    );
    $repo->order->dt_of_request = Carbon::create("2023-12-11T11:00:00");
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
    $hl7 = new Hl7();
    try {
        $hl7->setDatetimeFormat("YmdHis")
            ->setRepeatORC()
            ->setMsg($repo);
    } catch (Exception $e) {
        var_dump($e);
        die();
    }
    //expect($hl7->segments[5]->data[7][0][0][0])->toBeInstanceOf(Carbon::class);
    $out = $hl7->setDatetimeFormat("YmdHis")->write();
    expect($out)
        ->toBe('MSH|^~\&|agendasalt|SALT|Mirth|Test|20231211110000||ORM^001^ORM_001|123|P|2.5|||||NLD|8859/1' . chr(13) .
            'PID|1||123456782^^^NLMINBIZA^NNNLD~1234^^^ZorgDomein^VN||de Groot&de&Groot^A^^^^^L||20000101|M|||Schoonstraat 38 a&Schoonstraat&38^a^Amsterdam^^1000CC^NL^M||06 1234 5678^PRN^CP^test@mail.com||||||||||||||||||Y|NNNLD' . chr(13) .
            'PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V' . chr(13) .
            'PV2|||LABEDG001^laboratorium^99zda' . chr(13) .
            'IN1|1|^null|123^^^VEKTIS^UZOVI|||||||||||||||||||||||||||||||||123456789' . chr(13) .
            'ORC|NW|AB123||AB123|||^^^^^R||20231211110000|||12345678^de Groot^A^^^^^^VEKTIS' . chr(13) .
            'OBR|1|AB123||TST^Testname^SRC|R|||||||||||12345678^de Groot^A^^^^^^VEKTIS' . chr(13) .
            'OBX|1|ST|TST^Testname^SRC||123||||||F' . chr(13));


});