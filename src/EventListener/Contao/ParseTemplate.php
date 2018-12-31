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

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Contao;

use Contao\Template;

/**
 * Class ParseTemplate
 *
 * @package MenAtWork\MultiColumnWizardBundle\Contao\Events
 */
class ParseTemplate
{
    /**
     * @param Template $objTemplate
     */
    public function addScriptsAndStyles(&$objTemplate)
    {
        //do not allow version information to be leaked in the backend login and install tool (#184)
        if ($objTemplate->getName() != 'be_login' && $objTemplate->getName() != 'be_install') {

            $GLOBALS['TL_JAVASCRIPT'][] = $GLOBALS['TL_CONFIG']['debugMode']
                ? 'bundles/multicolumnwizard/js/multicolumnwizard_be_src.js'
                : 'bundles/multicolumnwizard/js/multicolumnwizard_be.js';

            $GLOBALS['TL_CSS'][] = $GLOBALS['TL_CONFIG']['debugMode']
                ? 'bundles/multicolumnwizard/css/multicolumnwizard_src.css'
                : 'bundles/multicolumnwizard/css/multicolumnwizard.css';

            $objTemplate->ua .= ' version_' . str_replace('.', '-', VERSION) . '-' . str_replace(
                    '.',
                    '-',
                    BUILD
                );
        }
    }
}
