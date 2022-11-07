<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Enums\PatientSexEnum;
use mmerlijn\msgRepo\Id;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;

class PID extends Segment implements SegmentInterface
{
    public string $name = "PID";

    public function setMsg(Msg $msg): void
    {
        //set patient identifier
        foreach ($msg->patient->ids as $k => $id) {
            $this->setData($id->id, 3, $k);
            $this->setData($id->authority, 3, $k, 3);
            $this->setData($id->code, 3, $k, 4);
        }
        //set patient name
        $this->setData(
            trim($msg->patient->name->prefix . " " . $msg->patient->name->lastname) .
            ($msg->patient->name->lastname ? " - " : "") .
            trim($msg->patient->name->own_prefix . " " . $msg->patient->name->own_lastname)
            , 5);
        $this->setData($msg->patient->name->own_prefix, 5, 0, 0, 1);
        $this->setData($msg->patient->name->own_lastname, 5, 0, 0, 2);
        $this->setData($msg->patient->name->prefix, 5, 0, 0, 3);
        $this->setData($msg->patient->name->lastname, 5, 0, 0, 4);
        $this->setData(mb_substr($msg->patient->name->initials, 0, 1), 5, 0, 1);
        $this->setData(preg_replace('/\./', "", mb_substr($msg->patient->name->initials, 1)), 5, 0, 2);

        //set dob
        $this->setData($msg->patient->dob?->format("Ymd"), 7);

        //set sex
        $this->setData($msg->patient->sex->value, 8);

        //set address
        $this->setData($msg->patient->address->street . " " . $msg->patient->address->building, 11);
        $this->setData($msg->patient->address->street, 11, 0, 0, 1);
        $this->setData($msg->patient->address->building_nr, 11, 0, 0, 2);
        $this->setData($msg->patient->address->building_addition, 11, 0, 1);
        $this->setData($msg->patient->address->city, 11, 0, 2);
        $this->setData($msg->patient->address->postcode, 11, 0, 4);
        $this->setData($msg->patient->address->country ?: "NL", 11, 0, 5);

        //set telephone
        foreach ($msg->patient->phones as $k => $phone) {
            $this->setData($phone, 13, $k);
            $this->setData(($k == 0) ? "PRN" : "ORN", 13, $k, 1);
            $this->setData("PH", 13, $k, 2);
        }


    }

    public function getMsg(Msg $msg): Msg
    {
        //get patient ID
        foreach ($this->data[2] as $k => $id) {
            $msg->patient->addId(
                new Id(
                    id: $this->getData(2, $k),
                    authority: $this->getData(2, $k, 3),
                    code: $this->getData(2, $k, 4)
                )
            );
        }
        //get patient identifier
        foreach ($this->data[3] as $k => $id) {
            $msg->patient->addId(
                new Id(
                    id: $this->getData(3, $k),
                    authority: $this->getData(3, $k, 3),
                    code: $this->getData(3, $k, 4)
                )
            );
        }
        //get name
        $msg->patient->setName(
            new Name(
                initials: $this->getInitials(),
                lastname: $this->getData(5, 0, 0, 4),
                own_lastname: $this->getData(5, 0, 0, 2),
                prefix: $this->getData(5, 0, 0, 3),
                own_prefix: $this->getData(5, 0, 0, 1),
            ));
        if (!$msg->patient->name->own_lastname) {
            $msg->patient->setName(new Name(
                name: $this->getData(5),
                initials: $this->getInitials()
            ));
        }
        //get dob
        $msg->patient->dob = $this->getDate(7);
        //get sex
        $msg->patient->sex = PatientSexEnum::set($this->getData(8));
        //get address
        $msg->patient->setAddress(new Address(
            street: $this->getData(11, 0, 0, 1),
            postcode: preg_replace('/\s/', '', $this->getData(11, 0, 4)),
            city: $this->getData(11, 0, 2),
            building: $this->getData(11, 0, 0, 2) . $this->getData(11, 0, 1),
            country: $this->getData(11, 0, 5),
        ));
        if (!$msg->patient->address->street) {
            $before = '/(?=.)\s' . $msg->patient->address->building_nr . '.*/';
            $msg->patient->address->street = preg_replace($before, "", $this->getData(11));
        }
        //second address
        if (isset($this->data[11][1])) {
            $msg->patient->setAddress2(new Address(
                street: $this->getData(11, 1, 0, 1),
                postcode: $this->getData(11, 1, 4),
                city: $this->getData(11, 1, 2),
                building: $this->getData(11, 1, 0, 2) . $this->getData(11, 1, 1),
                country: $this->getData(11, 1, 5),
            ));
        }
        //get phone
        if (isset($this->data[13])) {
            foreach ($this->data[13] as $k => $phone) {
                $msg->patient->addPhone($this->getData(13, $k));
            }
        }
        return $msg;
    }

    private function getInitials(): string
    {

        $first_name = preg_replace('/\s|\./', "", $this->getData(5, 0, 1));
        $initials = preg_replace('/\s|\./', "", $this->getData(5, 0, 2));
        if (mb_strlen($first_name) > 1) {

            //look for initials written as name same as firstname
            if (mb_strpos(mb_strtoupper($initials), mb_strtoupper($first_name)) !== false) {
                //trim firstnames from initials
                $initials = preg_replace('/' . $first_name . '/i', "", $initials);
            }
            if (preg_match('/[a-z]/', $first_name[1])) {
                $first_name = $first_name[0];
                if (($initials[0] ?? "") == $first_name[0]) {
                    $initials = mb_substr($initials, 1);
                }
            } else {
                if ($initials == $first_name) {
                    $first_name = "";
                }
            }
        } elseif (mb_strtoupper($first_name) == mb_strtoupper($initials)) {
            $initials = "";
        }
        return $first_name . $initials;

    }

    public function validate(): void
    {
        Validator::validate([
            "patient_identifier" => $this->data[3][0][0][0] ?? "",
            "patient_name" => $this->data[5][0][0][0] ?? "",
            "patient_dob" => $this->data[7][0][0][0] ?? "",
            "patient_sex" => $this->data[8][0][0][0] ?? "",
            "patient_address" => $this->data[11][0][0][0] ?? "",
            "patient_postcode" => $this->data[11][0][4][0] ?? "",
            "patient_city" => $this->data[11][0][2][0] ?? "",
        ], [
            "patient_identifier" => 'required',
            "patient_name" => 'required',
            "patient_dob" => 'required',
            "patient_sex" => 'required',
            "patient_address" => 'required',
            "patient_postcode" => 'required',
            "patient_city" => 'required',
        ], [
            "patient_identifier" => '@ PID[3][0][0][0] set/adjust $msg->patient->ids',
            "patient_name" => '@ PID[5][0][0][0] set/adjust $msg->patient->name',
            "patient_dob" => '@ PID[7][0][0][0] set/adjust $msg->patient->dob',
            "patient_sex" => '@ PID[8][0][0][0] set/adjust $msg->patient->sex',
            "patient_address" => '@ PID[11][0][0][0] set/adjust $msg->patient->address',
            "patient_postcode" => '@ PID[11][0][4][0] set/adjust $msg->patient->postcode',
            "patient_city" => '@ PID[11][0][2][0] set/adjust $msg->patient->city',
        ]);
    }
}

