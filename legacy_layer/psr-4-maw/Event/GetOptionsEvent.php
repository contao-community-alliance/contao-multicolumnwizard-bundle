<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 26.11.2018
 * Time: 17:52
 */

namespace MenAtWork\MultiColumnWizard\Event;

use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent as BundleGetOptionsEvent;

/**
 * Class GetOptionsEvent
 *
 * @package    MenAtWork\MultiColumnWizard\Event
 *
 * @deprecated Use instead \MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent
 */
class GetOptionsEvent extends BundleGetOptionsEvent
{
    /**
     * @inheritdoc
     */
    public function __construct(
        $propertyName,
        $subPropertyName,
        $environment,
        $model,
        $widget,
        array $options = array()
    ) {
        trigger_error(
            sprintf(
                'Use of deprecated class %s. Use instead %s',
                __CLASS__,
                BundleGetOptionsEvent::class
            )
        );

        parent::__construct($propertyName, $subPropertyName, $environment, $model, $widget, $options);
    }
}
