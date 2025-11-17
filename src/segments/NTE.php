<?php

namespace mmerlijn\msgHl7\segments;

use mmerlijn\msgHl7\validation\Validator;
use mmerlijn\msgRepo\Comment;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\TestCode;

class NTE extends Segment implements SegmentInterface
{
    public string $name = "NTE";

    public function getComment(Msg $msg, string $prev_segment): Msg
    {

        $comment = new Comment(
            text: $this->getData(3),
            source: $this->getData(2),
            type: new TestCode(
                code: $this->getData(4),
                value: $this->getData(4, 0, 1),
                source: $this->getData(4, 0, 2)),
        );
        switch ($prev_segment) {
            case "MSH":
                //add comment to msg
                $msg->addComment($comment);
                return $msg;
            case "PID":
                //add comment to patient
                $msg->patient->addComment($comment);
                return $msg;
            case "OBR":
                //add comment to order
                $msg->order->requests[count($msg->order->requests) - 1]->addComment($comment);
                return $msg;
            case "SPM":
                //add comment to order
                $req_index = count($msg->order->requests) - 1;
                $spec_index = count($msg->order->requests[$req_index]->specimens) - 1;
                $msg->order->requests[$req_index]->specimens[$spec_index]->addComment($comment);
                return $msg;
            case "OBX":
                $req_index = count($msg->order->requests) - 1;
                $obs_index = count($msg->order->requests[$req_index]->observations) - 1;
                $msg->order->requests[$req_index]->observations[$obs_index]->addComment($comment);
                return $msg;
        }
        return $msg;
    }

    //for testing purposes only
    public function setMsg(Msg $msg): self
    {
        $this->setComment(0, $msg->comments[0]);
        return $this;
    }

    public function setComment(int $id, Comment $comment): self
    {
        $this->setData($comment->source, 2);
        $this->setData($comment->text, 3);
        $this->setData($id + 1, 1);
        $this->setData($comment->type->code, 4);
        $this->setData($comment->type->value, 4, 0, 1);
        $this->setData($comment->type->source, 4, 0, 2);
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