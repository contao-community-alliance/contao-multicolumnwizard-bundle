<?php
/**
 * Created by PhpStorm.
 * User: Stefan Heimes
 * Date: 02.12.2018
 * Time: 10:57
 */

namespace MenAtWork\MultiColumnWizardBundle\Event;

use Contao\DataContainer;
use Contao\Widget;

/**
 * Class CreateNewRow
 *
 * @package MenAtWork\MultiColumnWizardBundle\Event
 */
class CreateWidget
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard.create-widget';

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