<?php

/**
 * This file is part of menatwork/contao-multicolumnwizard-bundle.
 *
 * (c) 2012-2019 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/contao-multicolumnwizard-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MultiColumnWizard\Event;

use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent as BundleGetOptionsEvent;

/**
 * Class GetOptionsEvent
 *
 * @deprecated Use instead \MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent
 */
class GetOptionsEvent extends BundleGetOptionsEvent
{
    /**
     * Name of this event.
     */
    const NAME = 'men-at-work.multi-column-wizard.get-options';

    /**
     * {@inheritDoc}
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
                'Use of deprecated class "%s". Use instead "%s"',
                __CLASS__,
                BundleGetOptionsEvent::class
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($propertyName, $subPropertyName, $environment, $model, $widget, $options);
    }
}
