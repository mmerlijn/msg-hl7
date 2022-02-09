# HL7

Read and write HL7 messages from and to array

### Requirements

```php >=8.1```

### Installation

``` composer require mmerlijn/msg-hl7```

### Writing messages

```php
// fill the msg repository
$msg = new Msg();
$msg->sender->application = "Application name";
$msg->sender->facility = "Facility name";
$msg->receiver->application = "Application name";
$msg->receiver->facility = "Facility name";
$msg->datetime = Carbon::now(); //default current datetime
$msg->security_id = ""; //optional
$msg->msgType->type = "ORM";
$msg->msgType->trigger = "001";
$msg->msgType->structure = "ORM_001";
$msg->id = "abc123"; //unique message id
$msg->msgType->version="2.4"; //default

//Patient data
$msg->patient->addId(new Id(id:"123456782",type:"bsn"));
$msg->patient->setName(new Name(
    own_lastname:"Doe",initials:"J"
));
$msg->patient->setSex("M");
$msg->patient->dob = Carbon::create("2000-10-05");
$msg->patient->setAddress(new Address(
   street: "Long Street",building: "14a",city: "Amsterdam",postcode: "1040AA"
   ));
$msg->patient->addPhone("0612341234");
$msg->patient->setInsurance(new Insurance(
            company_name: "CC Comp",
            policy_nr: "01234123124",
            uzovi: "1234",
        ));

//order data        
$msg->order->admit_reason_code = "ABC";
$msg->order->admit_reason_name = "Xohabia";

$msg->order->control ="NEW"; //NEW / CANCEL / CHANGE / RESULT
$msg->order->request_nr = "AB123123123";
$msg->priority = false; 
$msg->db_of_request = Carbon::now();
$msg->order->requester->agbcode = "0123456";
$msg->order->requester->setName(new Name(own_lastname: 'Arts',initials:"RP"));;
$msg->order->requester->source = "VEKTIS";

//requests
$msg->order->addRequest(new Request(
    test_code: "BBB", test_name: "Blubber"
));
$msg->order->where = "home"; // home=>L / other / else =>O

//result
$msg->order->addResult(new Result(
    type_of_value:"ST", //optional ST/NM/CE/FT
    test_code: "CCC",
    test_name: "Circular",
    value: "true",
    done: true, //final value
    change:false,
));
$msg->order->dt_of_observation = Carbon::create(...);
$msg->order->dt_of_analysis = Carbon::create(...);

//comments
$msg->addComment("Hello World"); //belongs to msg

$msg->order->requests->addComment("Hello Day"); // comment on request

$msg->order->result->addComment("Good morning") // comment on result


//create HL7 instance
$hl7 = new \mmerlijn\msgHl7\Hl7();

//setting the data
$hl7->setDatetimeFormat("YmdHis") //option (how to write datetime values 
    ->setRepeatORC() //option default only ones
    ->setMsg($msg);

//
try{
  echo $hl7->write(true); //with or without validation of required fields
}catch(\Exception $e){
   echo $e;
}
```

It is also possible to start with a template and add/overwrite msg data afterwards

### Getting message

```php
//init instance
$hl7 = new \mmerlijn\msgHl7\Hl7("MSH ....");

//or
$hl7 = new \mmerlijn\msgHl7\Hl7();
$hl7->read("MSH...");

//read data to repository
$msg = $hl7->getMsg(new Msg());

```

### Result

```php
//Input
MSH|^~\&|ZorgDomein||OrderModule||20220102161545+0200||ORM^O01^ORM_O01|e49ce31d|P|2.4|||||NLD|8859/1
PID|1||123456782^^^NLMINBIZA^NNNLD~ZD12345678^^^ZorgDomein^VN||Testname&&Testname^A^B^^^^L||19800623|M|||Schoonstraat 38 a&Schoonstraat&38^A^AMSTERDAM^^1040AB^NL^M||0612341234^ORN^CP||||||||||||||||||Y|NNNLD
PV1|1|O|||||||||||||||||||||||||||||||||||||||||||||||||V
PV2|||LABEDG001^laboratorium^99zda
IN1|1|^null|123^^^VEKTIS^UZOVI|Ditzo Zorgverzekering||||||||||||||||||||||||||||||||123456789
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|1|ZD12345678||CRP^CRP^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS
OBX|1|ST|COVIDSYM^Covid-19 verdacht^99zdl||false||||||F
OBX|2|CE|COVIDURG^Urgentie?^99zdl||true^Urgent (vandaag best effort NIET CITO)^99zda||||||F
ORC|NW|ZD12345678||ZD12345678|||^^^^^R||20220102103000+0200|^Doe^J.||01123456^van der Plas^B.^^^^^^VEKTIS|^^^Huisartsenpraktijk van der Plas&01123456^^^^^Huisartsenpraktijk van der Plas||||01123456^Huisartsenpraktijk van der Plas^VEKTIS||||Huisartsenpraktijk van der Plas^^01123456^^^VEKTIS
OBR|2|ZD12345678||TIJD^TIJD^99zdl|||||||O|||||01123456^van der Plas^R.^^^^^^VEKTIS

//output ($msg)
array(10) {
  ["patient"]=>
  array(9) {
    ["sex"]=>
    string(1) "M"
    ["name"]=>
    array(6) {
      ["initials"]=>
      string(2) "AB"
      ["lastname"]=>
      string(0) ""
      ["prefix"]=>
      string(0) ""
      ["own_lastname"]=>
      string(8) "Testname"
      ["own_prefix"]=>
      string(0) ""
      ["name"]=>
      string(11) "AB Testname"
    }
    ["dob"]=>
    string(10) "1980-06-23"
    ["bsn"]=>
    string(9) "123456782"
    ["address"]=>
    array(8) {
      ["postcode"]=>
      string(6) "1040AB"
      ["city"]=>
      string(9) "Amsterdam"
      ["street"]=>
      string(12) "Schoonstraat"
      ["building"]=>
      string(4) "38 A"
      ["building_nr"]=>
      string(2) "38"
      ["building_addition"]=>
      string(1) "A"
      ["postbus"]=>
      string(0) ""
      ["country"]=>
      string(2) "NL"
    }
    ["address2"]=>
    NULL
    ["phones"]=>
    array(1) {
      [0]=>
      string(10) "0612341234"
    }
    ["insurance"]=>
    array(5) {
      ["uzovi"]=>
      string(0) ""
      ["policy_nr"]=>
      string(0) ""
      ["company_name"]=>
      string(0) ""
      ["phone"]=>
      string(0) ""
      ["address"]=>
      NULL
    }
    ["ids"]=>
    array(2) {
      [0]=>
      array(4) {
        ["id"]=>
        string(9) "123456782"
        ["authority"]=>
        string(9) "NLMINBIZA"
        ["type"]=>
        string(3) "bsn"
        ["code"]=>
        string(5) "NNNLD"
      }
      [1]=>
      array(4) {
        ["id"]=>
        string(10) "ZD12345678"
        ["authority"]=>
        string(10) "ZorgDomein"
        ["type"]=>
        string(0) ""
        ["code"]=>
        string(2) "VN"
      }
    }
  }
  ["order"]=>
  array(16) {
    ["control"]=>
    string(3) "NEW"
    ["request_nr"]=>
    string(10) "ZD12345678"
    ["lab_nr"]=>
    string(0) ""
    ["complete"]=>
    bool(true)
    ["priority"]=>
    bool(false)
    ["order_status"]=>
    string(0) ""
    ["where"]=>
    string(5) "other"
    ["requester"]=>
    array(10) {
      ["agbcode"]=>
      string(8) "01123456"
      ["source"]=>
      string(6) "VEKTIS"
      ["name"]=>
      array(6) {
        ["initials"]=>
        string(1) "B"
        ["lastname"]=>
        string(0) ""
        ["prefix"]=>
        string(0) ""
        ["own_lastname"]=>
        string(4) "Plas"
        ["own_prefix"]=>
        string(7) "van der"
        ["name"]=>
        string(12) "van der Plas"
      }
      ["address"]=>
      NULL
      ["phone"]=>
      string(0) ""
      ["type"]=>
      string(0) ""
      ["organisation"]=>
      NULL
      ["application"]=>
      string(0) ""
      ["device"]=>
      string(0) ""
      ["facility"]=>
      string(0) ""
    }
    ["copy_to"]=>
    array(10) {
      ["agbcode"]=>
      string(0) ""
      ["source"]=>
      string(0) ""
      ["name"]=>
      array(6) {
        ["initials"]=>
        string(0) ""
        ["lastname"]=>
        string(0) ""
        ["prefix"]=>
        string(0) ""
        ["own_lastname"]=>
        string(0) ""
        ["own_prefix"]=>
        string(0) ""
        ["name"]=>
        string(0) ""
      }
      ["address"]=>
      NULL
      ["phone"]=>
      string(0) ""
      ["type"]=>
      string(0) ""
      ["organisation"]=>
      NULL
      ["application"]=>
      string(0) ""
      ["device"]=>
      string(0) ""
      ["facility"]=>
      string(0) ""
    }
    ["dt_of_request"]=>
    string(19) "2022-01-02 10:30:00"
    ["dt_of_observation"]=>
    NULL
    ["dt_of_observation_end"]=>
    NULL
    ["dt_of_analysis"]=>
    NULL
    ["results"]=>
    array(2) {
      [0]=>
      array(15) {
        ["value"]=>
        string(5) "false"
        ["type_of_value"]=>
        string(0) ""
        ["units"]=>
        string(0) ""
        ["test_code"]=>
        string(8) "COVIDSYM"
        ["test_name"]=>
        string(17) "Covid-19 verdacht"
        ["test_source"]=>
        string(5) "99zdl"
        ["other_test_code"]=>
        string(0) ""
        ["other_test_name"]=>
        string(0) ""
        ["other_test_source"]=>
        string(0) ""
        ["quantity"]=>
        string(0) ""
        ["reference_range"]=>
        string(0) ""
        ["abnormal_flag"]=>
        string(0) ""
        ["comments"]=>
        array(0) {
        }
        ["done"]=>
        string(1) "Y"
        ["change"]=>
        string(1) "N"
      }
      [1]=>
      array(15) {
        ["value"]=>
        string(4) "true"
        ["type_of_value"]=>
        string(0) ""
        ["units"]=>
        string(0) ""
        ["test_code"]=>
        string(8) "COVIDURG"
        ["test_name"]=>
        string(9) "Urgentie?"
        ["test_source"]=>
        string(5) "99zdl"
        ["other_test_code"]=>
        string(0) ""
        ["other_test_name"]=>
        string(38) "Urgent (vandaag best effort NIET CITO)"
        ["other_test_source"]=>
        string(5) "99zda"
        ["quantity"]=>
        string(0) ""
        ["reference_range"]=>
        string(0) ""
        ["abnormal_flag"]=>
        string(0) ""
        ["comments"]=>
        array(0) {
        }
        ["done"]=>
        string(1) "Y"
        ["change"]=>
        string(1) "N"
      }
    }
    ["requests"]=>
    array(2) {
      [0]=>
      array(8) {
        ["test_code"]=>
        string(3) "CRP"
        ["test_name"]=>
        string(3) "CRP"
        ["test_source"]=>
        string(5) "99zdl"
        ["other_test_code"]=>
        string(0) ""
        ["other_test_name"]=>
        string(0) ""
        ["other_test_source"]=>
        string(0) ""
        ["comments"]=>
        array(0) {
        }
        ["change"]=>
        string(1) "N"
      }
      [1]=>
      array(8) {
        ["test_code"]=>
        string(4) "TIJD"
        ["test_name"]=>
        string(4) "TIJD"
        ["test_source"]=>
        string(5) "99zdl"
        ["other_test_code"]=>
        string(0) ""
        ["other_test_name"]=>
        string(0) ""
        ["other_test_source"]=>
        string(0) ""
        ["comments"]=>
        array(0) {
        }
        ["change"]=>
        string(1) "N"
      }
    }
    ["comments"]=>
    array(0) {
    }
  }
  ["sender"]=>
  array(10) {
    ["agbcode"]=>
    string(0) ""
    ["source"]=>
    string(0) ""
    ["name"]=>
    array(6) {
      ["initials"]=>
      string(0) ""
      ["lastname"]=>
      string(0) ""
      ["prefix"]=>
      string(0) ""
      ["own_lastname"]=>
      string(0) ""
      ["own_prefix"]=>
      string(0) ""
      ["name"]=>
      string(0) ""
    }
    ["address"]=>
    NULL
    ["phone"]=>
    string(0) ""
    ["type"]=>
    string(0) ""
    ["organisation"]=>
    NULL
    ["application"]=>
    string(10) "ZorgDomein"
    ["device"]=>
    string(0) ""
    ["facility"]=>
    string(0) ""
  }
  ["receiver"]=>
  array(10) {
    ["agbcode"]=>
    string(0) ""
    ["source"]=>
    string(0) ""
    ["name"]=>
    array(6) {
      ["initials"]=>
      string(0) ""
      ["lastname"]=>
      string(0) ""
      ["prefix"]=>
      string(0) ""
      ["own_lastname"]=>
      string(0) ""
      ["own_prefix"]=>
      string(0) ""
      ["name"]=>
      string(0) ""
    }
    ["address"]=>
    NULL
    ["phone"]=>
    string(0) ""
    ["type"]=>
    string(0) ""
    ["organisation"]=>
    NULL
    ["application"]=>
    string(11) "OrderModule"
    ["device"]=>
    string(0) ""
    ["facility"]=>
    string(0) ""
  }
  ["datetime"]=>
  string(19) "2022-01-02 16:15:45"
  ["msgType"]=>
  array(4) {
    ["type"]=>
    string(3) "ORM"
    ["trigger"]=>
    string(3) "O01"
    ["structure"]=>
    string(7) "ORM_O01"
    ["version"]=>
    string(3) "2.4"
  }
  ["id"]=>
  string(8) "e49ce31d"
  ["security_id"]=>
  string(0) ""
  ["processing_id"]=>
  string(1) "P"
  ["comments"]=>
  array(0) {
  }
}
```