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

use Contao\System;
use Contao\Template;
use MenAtWork\MultiColumnWizardBundle\Service\ContaoApiService;

/**
 * Class ParseTemplate
 */
class ParseTemplate
{
    /**
     * @var ContaoApiService
     */
    private ContaoApiService $contaoApi;

    public function __construct()
    {
        $this->contaoApi = System::getContainer()->get(ContaoApiService::class);
    }

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
    public function addVersion(Template &$objTemplate): void
    {
        // Do not allow version information to be leaked in the backend login and install tool (#184)
        if ('be_login' === $objTemplate->getName() || 'be_install' === $objTemplate->getName()) {
            return;
        }

        // Only run for the backend. I don't know if we need this information in the FE as well.
        // ToDo: Check if we need this in FE, if so we should add this information other, 'cause of security.
        if (!$this->contaoApi->isBackend()) {
            return;
        }

        $objTemplate->ua .= ' version_' . str_replace('.', '-', $this->contaoApi->getContaoVersion());
    }
}
