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

namespace MenAtWork\MultiColumnWizardBundle\Event;

use Contao\DataContainer;
use Contao\Widget;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CreateWidgetEvent
 */
class CreateWidgetEvent extends Event
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard-bundle.create-widget';

    /**
     * @var DataContainer
     */
    private $dcDriver;

    /**
     * @var Widget
     */
    private $widget;

    /**
     * CreateNewRow constructor.
     *
     * @param DataContainer $dcDriver
     */
    public function __construct($dcDriver)
    {
        $this->dcDriver = $dcDriver;
    }

    /**
     * @return DataContainer
     */
    public function getDcDriver()
    {
        return $this->dcDriver;
    }

    /**
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param Widget $widget
     *
     * @return CreateWidget
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;

        return $this;
    }
}
