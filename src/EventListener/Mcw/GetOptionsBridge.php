<?php

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use MenAtWork\MultiColumnWizard\Event\GetOptionsEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent as GetOptionEventBundle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class GetOptionsBridge
 *
 * @package MenAtWork\MultiColumnWizardBundle\EventListener\Mcw
 */
class GetOptionsBridge
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ExecutePostActions constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check if we have any listeners for the old events.
     *
     * @return bool
     */
    private function hasListeners()
    {
        $listeners = $this
            ->eventDispatcher
            ->getListeners('men-at-work.multi-column-wizard.get-options');

        return !empty($listeners);
    }

    /**
     * Listener for building the tiny mce.
     *
     * @param GetOptionEventBundle $event
     *
     * @return void
     */
    public function executeEvent(GetOptionEventBundle $event)
    {
        // Check if we have some old events.
        if (!$this->hasListeners()) {
            return;
        }

        $eventOld = new GetOptionsEvent(
            $event->getPropertyName(),
            $event->getSubPropertyName(),
            $event->getEnvironment(),
            $event->getModel(),
            $event->getWidget(),
            $event->getOptions()
        );
        $this->eventDispatcher->dispatch($eventOld::NAME, $eventOld);

        if ($eventOld->getOptions() !== $event->getOptions()) {
            $event->setOptions($eventOld->getOptions());
        }
    }
}