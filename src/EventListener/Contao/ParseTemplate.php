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

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Contao;

use Contao\Template;

/**
 * Class ParseTemplate
 */
class ParseTemplate
{
    /**
     * Add the scripts and stylesheet to the passed template.
     *
     * @param Template $objTemplate The template to add to.
     *
     * @return void
     *
     * @@SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function addScriptsAndStyles(&$objTemplate)
    {
        // do not allow version information to be leaked in the backend login and install tool (#184)
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
