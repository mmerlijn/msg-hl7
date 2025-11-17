<?php

namespace mmerlijn\msgHl7;

class ZorgomeinOrder extends Hl7
{
    protected array $useSegments=[
        "MSH",
        "PID",
        "PV1",
        "PV2",
        "IN1",
        "ORC",
        "OBR",
        "OBX",
        "NTE",
        "TQ1",
        "SPM",
        "LBS"
    ];
    public function getLbsError():bool
    {
        return $this->segments[$this->findSegmentKey("LBS")]?->getLbsError();
    }
    public function getLbsErrorMsg():bool
    {
        return $this->segments[$this->findSegmentKey("LBS")]?->getLbsErrorMsg();
    }
    public function getLbsErrorLvl(): string
    {
        return $this->segments[$this->findSegmentKey("LBS")]?->getLbsErrorLvl();
    }
}