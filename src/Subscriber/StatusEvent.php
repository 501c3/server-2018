<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/18/18
 * Time: 6:03 PM
 */

namespace App\Subscriber;

use Symfony\Component\EventDispatcher\Event;

class StatusEvent extends Event
{
    const NAME = 'status.update';
    /** @var Status */
    private $status;

    public function __construct(Status $status)
    {
        $this->status=$status;
    }

    public function getStatus()
    {
        return $this->status;
    }

}