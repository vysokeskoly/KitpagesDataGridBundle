<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Event;

class DataGridEvent extends AbstractEvent
{
    public function __construct(self $event = null)
    {
        if ($event) {
            $this->data = $event->data;
            $this->isDefaultPrevented = $event->isDefaultPrevented;
            $this->isPropagationStopped = $event->isPropagationStopped;
        }
    }
}
