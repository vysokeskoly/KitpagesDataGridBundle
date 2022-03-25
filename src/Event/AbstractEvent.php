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

    /**
     * @param mixed $key
     * @param mixed $val
     */
    public function set($key, $val): void
    {
        $this->data[$key] = $val;
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }

        return $this->data[$key];
    }
}
