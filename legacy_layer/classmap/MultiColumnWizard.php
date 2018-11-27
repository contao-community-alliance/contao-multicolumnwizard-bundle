<?php

use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard as BundleMultiColumnWizard;

/**
 * Class MultiColumnWizard
 *
 * @package    MultiColumnWizard
 *
 * @deprecated Deprecated and will be removed in version 4. Use
 *             MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard
 */
class MultiColumnWizard extends BundleMultiColumnWizard
{
    /**
     * MultiColumnWizard constructor.
     *
     * @param bool $arrAttributes
     */
    public function __construct($arrAttributes = false)
    {
        trigger_error(
            sprintf(
                'Use of deprecated class %s. Use instead %s',
                __CLASS__,
                BundleMultiColumnWizard::class
            )
        );

        parent::__construct($arrAttributes);
    }
}
