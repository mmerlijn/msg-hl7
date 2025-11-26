<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgRepo\Msg;

class LBS extends Segment implements SegmentInterface
{
    public string $name = "LBS";

    public function getLbsError(): bool
    {
        return $this->getData(1) == 'ERROR';
    }

    public function getLbsErrorMsg(): string
    {
        return $this->getData(3);
    }

    public function getLbsErrorLvl(): string
    {
        return (int)$this->getData(2);
    }

    public function getLbsNr(): string
    {
        return (int)$this->getData(4);
    }

    public function getLbsSex(): string
    {
        return (int)$this->getData(5);
    }

    public function setLbsError(bool $isError = true): self
    {
        if ($isError) {
            $this->setData(1, 'ERROR');
        } else {
            $this->setData(1, 'GOED');
        }
        return $this;
    }

    public function setLbsErrorMsg(string $msg): self
    {
        $this->setData(3, $msg);
        return $this;
    }

    public function setLbsErrorLvl(int $lvl): self
    {
        $this->setData(2, (string)$lvl);
        return $this;
    }

}