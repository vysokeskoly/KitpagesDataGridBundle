<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Kitpages\DataGridBundle\Event\DataGridEvent;
use Kitpages\DataGridBundle\KitpagesDataGridEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Created by Philippe Le Van.
 * Date: 09/04/13
 */
class ConversionSubscriber implements EventSubscriberInterface
{
    private bool $isDefaultPrevented = false;
    private bool $afterActivated = false;

    public static function getSubscribedEvents(): array
    {
        return [
            KitpagesDataGridEvents::ON_DISPLAY_GRID_VALUE_CONVERSION => 'onConversion',
            KitpagesDataGridEvents::AFTER_DISPLAY_GRID_VALUE_CONVERSION => 'afterConversion',
        ];
    }

    public function setIsDefaultPrevented(bool $val): void
    {
        $this->isDefaultPrevented = $val;
    }

    public function setAfterActivated(bool $val): void
    {
        $this->afterActivated = $val;
    }

    public function onConversion(DataGridEvent $event): void
    {
        if ($this->isDefaultPrevented) {
            $event->preventDefault();
            $event->set('returnValue', $event->get('field')->getFieldName() . ';preventDefault;' . $event->get('value'));
        } else {
            $row = $event->get('row');
            $event->set('value', $row['node.id'] . ';' . $event->get('value'));
        }
    }

    public function afterConversion(DataGridEvent $event): void
    {
        if (!$this->afterActivated) {
            return;
        }
        $event->set('returnValue', 'after;' . $event->get('returnValue'));
    }
}
