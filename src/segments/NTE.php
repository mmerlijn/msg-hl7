<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Result;

class NTE extends Segment implements SegmentInterface
{
    public string $name = "NTE";

    public function getMsg(Msg $msg): Msg
    {
        if (!empty($msg->order->results)) {
            //add comment
            $msg->order->results[count($msg->order->results) - 1]->addComment($this->getData(3));
        } elseif (!empty($msg->order->requests)) {
            $msg->order->requests[count($msg->order->requests) - 1]->addComment($this->getData(3));
        } else {
            $msg->addComment($this->getData(3));
        }
        return $msg;
    }

    public function setComment(int $id, string $comment): self
    {
        $this->setData($comment, 3);
        $this->setData($id + 1, 1);
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "comment" => $this->data[3][0][0][0] ?? "",
        ], [
            "comment" => 'required',
        ], [
            "comment" => '@ NTE[3][0][0][0] set/adjust $msg->comments / $msg->order->requests[..]->comments / $msg->order->results[..]->comments',
        ]);
    }
}