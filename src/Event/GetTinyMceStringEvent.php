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

/**
 * Class GetTinyMceEvent
 */
class GetTinyMceStringEvent extends GetStringEvent
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard-bundle.get-tiny-mce';

    /**
     * The tiny MCE initialization string.
     *
     * @var string
     */
    private $tinyMce;

    /**
     * Set the TinyMce string.
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
