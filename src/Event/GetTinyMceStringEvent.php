<?php

/**
 * This file is part of MultiColumnWizard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MultiColumnWizard
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  Andreas Schempp 2011
 * @copyright  certo web & design GmbH 2011
 * @copyright  MEN AT WORK 2013
 * @license    LGPL
 */

namespace MenAtWork\MultiColumnWizardBundle\Event;

use Contao\DataContainer;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetTinyMceEvent
 *
 * @package MenAtWork\MultiColumnWizardBundle\Event
 */
class GetTinyMceStringEvent extends GetStringEvent
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard.get-tiny-mce';

    /**
     * @var string
     */
    private $tinyMce;

    /**
     * The TinyMce string.
     *
     * @param string $string The TinyMce string.
     *
     * @return $this
     */
    public function setTinyMce($string)
    {
        $this->tinyMce = $string;

        return $this;
    }

    /**
     * Get the TinyMce string.
     *
     * @return string
     */
    public function getTinyMce()
    {
        return $this->tinyMce;
    }
}