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
    public function supportModalSelector($strTable)
    {
        if (strpos(\Environment::get('script'), 'contao/file.php') !== false
            || strpos(\Environment::get('script'), 'contao/page.php') !== false) {
            list($strField, $strColumn) = explode('__', \Input::get('field'));
            if ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'] == 'multiColumnWizard') {
                $GLOBALS['TL_DCA'][$strTable]['fields'][$strField . '__' . $strColumn] =
                    $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['columnFields'][$strColumn];
            }
        }
    }
}