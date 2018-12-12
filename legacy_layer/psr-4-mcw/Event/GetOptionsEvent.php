<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 26.11.2018
 * Time: 17:52
 */

namespace MultiColumnWizard\Event;

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
        $options = null
    ) {
        trigger_error(
            sprintf(
                'Use of deprecated class %s. Use instead %s',
                __CLASS__,
                BundleGetOptionsEvent::class
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($propertyName, $subPropertyName, $environment, $model, $widget, $options);
    }
}
