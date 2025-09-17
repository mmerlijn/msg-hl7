<?php

namespace mmerlijn\msgHl7\tests\Unit\segments;

use Carbon\Carbon;
use mmerlijn\msgHl7\Hl7;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Id;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;

class PIDTest extends \mmerlijn\msgHl7\tests\TestCase
{
    public function test_setters()
    {
        $msg = new Msg();
        $msg->patient->addId(new Id(id: "123456782", authority: "NLMINBIZA", code: "NNNLD"));
        $msg->patient->addId(new Id(id: "ZD12345678", authority: "ZorgDomein", code: "VN"));
        $msg->patient->addId(new Id(id: "ABC1010200001", authority: "SALT", code: "PI"));

        //name
        $msg->patient->setName(new Name(initials: "P.D.", lastname: "Hek", prefix: "van 't", own_lastname: "Vries", own_prefix: "de"));
        //dob
        $msg->patient->dob = Carbon::create("2000-10-12");
        //sex
        $msg->patient->setSex("F");
        //address
        $msg->patient->setAddress(new Address(street: "Schoonstraat", building: "38a", city: "Amsterdam", postcode: "1000CC"));
        //phone
        $msg->patient->addPhone("0612345678");
        $msg->patient->addPhone("0201234567");

        $msg->patient->email = "fake@mail.com";

        $hl7 = (new Hl7())->setMsg($msg);
        //var_dump($hl7->segments[0]->data);
        $string = $hl7->write();

        $this->assertStringContainsString("PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN|ABC1010200001^^^SALT^PI", $string);
        $this->assertStringContainsString("van 't Hek - de Vries&de&Vries&van 't&Hek^P^D^^^^L|", $string);
        $this->assertStringContainsString("|20001012|", $string);
        $this->assertStringContainsString("|Schoonstraat 38 a&Schoonstraat&38^a^Amsterdam^^1000CC^NL^M|", $string);
        $this->assertStringContainsString("|06 1234 5678^PRN^CP~020 1234 567^ORN^PH~^NET^Internet^fake@mail.com", $string);

    }

    public function test_getters()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN|ABC2012199901^^^SALT^PI|Doe - Testname&&Testname&&Doe^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^a^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP~^NET^Internet^fake@mail.com||||||||||||||||||Y|NNNLD");
        $msg = $hl7->getMsg(new Msg());

        $this->assertSame("123456782", $msg->patient->ids[0]->id);
        $this->assertSame("NLMINBIZA", $msg->patient->ids[0]->authority);
        $this->assertSame("NNNLD", $msg->patient->ids[0]->code);
        $this->assertSame("ZD12345678", $msg->patient->ids[1]->id);
        $this->assertSame("ZorgDomein", $msg->patient->ids[1]->authority);
        $this->assertSame("VN", $msg->patient->ids[1]->code);
        $this->assertSame("ABC2012199901", $msg->patient->ids[2]->id);
        //sex
        $this->assertSame("M", $msg->patient->sex->value);
        //dob
        $this->assertSame("1980-06-23", $msg->patient->dob->format("Y-m-d"));
        //name
        $this->assertSame("Testname", $msg->patient->name->own_lastname);
        $this->assertSame("AB", $msg->patient->name->initials);
        $this->assertSame("Doe", $msg->patient->name->lastname);
        $this->assertSame("", $msg->patient->name->prefix);
        $this->assertSame("", $msg->patient->name->own_prefix);

        //address
        $this->assertSame("Schoonstraat", $msg->patient->address->street);
        $this->assertSame("38", $msg->patient->address->building_nr);
        $this->assertSame("a", $msg->patient->address->building_addition);
        $this->assertSame("Amsterdam", $msg->patient->address->city);
        $this->assertSame("1040AB", $msg->patient->address->postcode);
        $this->assertSame("NL", $msg->patient->address->country);

        //phone
        $this->assertSame("06 1234 1234", (string)$msg->patient->phones[0] ?? "");
        //Extra's

        //strange name
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Doe van- de Testname&&&&^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP~^NET^Internet^fake@mail.com||||||||||||||||||Y|NNNLD");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("Testname", $msg->patient->name->own_lastname);
        $this->assertSame("Doe", $msg->patient->name->lastname);
        $this->assertSame("van", $msg->patient->name->prefix);
        $this->assertSame("de", $msg->patient->name->own_prefix);

        //address 2
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Doe - Testname&&Testname&&Doe^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^a^AMSTERDAM^^1040AB^NL^M~2e Street 39&2e Street&39^^Amsterdam^^1040AA^NL^M||0612341234^ORN^CP~^NET^Internet^fake@mail.com||||||||||||||||||Y|NNNLD");
        $msg = $hl7->getMsg(new Msg());

        $this->assertSame("2e Street", $msg->patient->address2->street);
        $this->assertSame("39", $msg->patient->address2->building_nr);
        $this->assertSame("", $msg->patient->address2->building_addition);
        $this->assertSame("Amsterdam", $msg->patient->address2->city);
        $this->assertSame("1040AA", $msg->patient->address2->postcode);
        $this->assertSame("NL", $msg->patient->address2->country);

        $this->assertSame("fake@mail.com", $msg->patient->email);
    }

    public function test_initials_same_as_name()
    {
        //firstname^initials
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^Klaas^klaas^^^^L||19800101|M|^^^^^^A||Straat^219^Amsterdam^^1040AB^NL||06-12345678
");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("K", $msg->patient->name->initials);
    }

    public function test_initials_different_from_name()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^Klaas^B^^^^L||19800101|M|^^^^^^A||Straat^219^Amsterdam^^1040AB^NL||06-12345678
");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("KB", $msg->patient->name->initials);
    }

    public function test_initials_same_first_char()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^B^B^^^^L||19800101|M|^^^^^^A||Straat^219^Amsterdam^^1040AB^NL||06-12345678
");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("B", $msg->patient->name->initials);

    }

    public function test_name_initials_name_same_first_char_as_initial()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^Bob^B^^^^L||19800101|M|^^^^^^A||Straat^219^Amsterdam^^1040AB^NL||06-12345678
");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("B", $msg->patient->name->initials);
    }

    public function test_name_initials_name_and_more_initial()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^Bob^BR^^^^L||19800101|M|^^^^^^A||Straat^219^Amsterdam^^1040AB^NL||06-12345678
");
        $msg = $hl7->getMsg(new Msg());
        $this->assertSame("BR", $msg->patient->name->initials);
    }

    public function test_address_getter()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^Bob^BR^^^^L||19800101|M|^^^^^^A||Verbindingsdam 4 A/B M/S NE&Verbindingsdam 4^A/B M/S NE^Amsterdam^^1019BD^NL^M||0611112222^ORN^CP||||||||||||||||||Y|NNNLD
");
        $msg = $hl7->getMsg(new Msg());
        //var_dump($msg->patient->address);
        //exit();
        $this->assertSame("Verbindingsdam", $msg->patient->address->street);
    }

    public function test_email_getter()
    {
        $hl7 = new Hl7("MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Jansen^Bob^BR^^^^L||19800101|M|^^^^^^A||Verbindingsdam 4 A/B M/S NE&Verbindingsdam 4^A/B M/S NE^Amsterdam^^1019BD^NL^M||0611112222^ORN^CP~^NET^Internet^fake@mail.com||||||||||||||||||Y|NNNLD
");
        $msg = $hl7->getMsg(new Msg());
        //var_dump($msg->patient->address);
        //exit();
        $this->assertSame("fake@mail.com", $msg->patient->email);
    }

    public function test_email_setter()
    {
        $msg = new Msg();
        $msg->patient->email = "fake@mail.com";
        $hl7 = (new Hl7())->setMsg($msg);
        $out = $hl7->write();
        expect($out)->toContain("~^NET^Internet^fake@mail.com|");

    }
}