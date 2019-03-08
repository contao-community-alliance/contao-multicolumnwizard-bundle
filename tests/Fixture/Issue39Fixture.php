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
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Test\Fixture;

/**
 * This fixture is for testing against issue #39.
 */
class Issue39Fixture
{
    /**
     * This requires the MCW from via global namespace.
     *
     * @param \MultiColumnWizard $multiColumnWizard The MCW.
     *
     * @return void
     */
    public function testing(\MultiColumnWizard $multiColumnWizard)
    {
        // No-op.
    }
}
