<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    protected array $data = [];
    protected bool $isDefaultPrevented = false;
    protected bool $isPropagationStopped = false;

    public function preventDefault(): void
    {
        $this->isDefaultPrevented = true;
    }

    public function isDefaultPrevented(): bool
    {
        return $this->isDefaultPrevented;
    }

    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }

    public function set(mixed $key, mixed $val): void
    {
        $this->data[$key] = $val;
    }

    public function get(mixed $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }

        return $this->data[$key];
    }
}
