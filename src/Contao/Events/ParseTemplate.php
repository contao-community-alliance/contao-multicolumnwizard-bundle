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
            $GLOBALS['TL_JAVASCRIPT']['mcw'] = $GLOBALS['TL_CONFIG']['debugMode']
                ? 'system/modules/multicolumnwizard/html/js/multicolumnwizard_be_src.js'
                : 'system/modules/multicolumnwizard/html/js/multicolumnwizard_be.js';
            $GLOBALS['TL_CSS']['mcw']        = $GLOBALS['TL_CONFIG']['debugMode']
                ? 'system/modules/multicolumnwizard/html/css/multicolumnwizard_src.css'
                : 'system/modules/multicolumnwizard/html/css/multicolumnwizard.css';
            $objTemplate->ua                 .= ' version_' . str_replace('.', '-', VERSION) . '-' . str_replace(
                    '.',
                    '-',
                    BUILD
                );
        }
    }
}