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

namespace MenAtWork\MultiColumnWizardBundle\Contao\Events;

/**
 * Class LoadDataContainer
 *
 * @package MenAtWork\MultiColumnWizardBundle\Contao\Events
 */
class LoadDataContainer
{
    /**
     * Deprecated in Contao 4.x since we didn't have the data in access.
     *
     * @param string $strTable Name of the table.
     *
     * @deprecated Removed in version 4 of mcw. There is no replacement.
     */
    public function supportModalSelector($strTable)
    {
        // In previous versions of contao, we got the filed and could add the information to the list. But now, it
        // is not possible, because contao add the information into the url under extra. So we have
        // to add all on the right place when the widget generates the data.
    }
}
