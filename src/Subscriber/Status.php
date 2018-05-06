<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/19/18
 * Time: 7:57 PM
 */

namespace App\Subscriber;


class Status
{
    const COMMENCE      = 1;
    const WORKING       = 2;
    const COMPLETE      = 3;
    const ERRORS        = 4;

    /** @var int */
    private $status;

    /** @var \DateTime */
    private $timestamp;

    /** @var \DateTime */
    private $start;

    /** @var int  */
    private $progress;

    private $message;

    private $errors = [];

    public function __construct(string $status, int $progress,  string $message=null)
    {
        $this->timestamp = new \DateTime('now');
        $this->progress = $progress;
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getProgress(): int
    {
        return $this->progress;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */

    public function getErrors(): array
    {
        return $this->errors;
    }
}