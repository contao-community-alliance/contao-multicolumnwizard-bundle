<?php
/**
 * Created by PhpStorm.
 * User: Stefan Heimes
 * Date: 25.11.2018
 * Time: 15:48
 */

namespace MenAtWork\MultiColumnWizardBundle\Contao\Events;

use Contao\Template;

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