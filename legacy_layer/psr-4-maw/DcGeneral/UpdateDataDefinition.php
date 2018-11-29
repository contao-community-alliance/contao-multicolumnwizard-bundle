<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 27.11.2018
 * Time: 11:47
 */

namespace MenAtWork\MultiColumnWizard\DcGeneral;


use MenAtWork\MultiColumnWizardBundle\DcGeneral\UpdateDataDefinition as BundleUpdateDataDefinition;

/**
 * Class UpdateDataDefinition
 *
 * @package    MultiColumnWizard\DcGeneral
 *
 * @deprecated Deprecated and will be removed in version 4. Use
 *             MenAtWork\MultiColumnWizardBundle\DcGeneral\UpdateDataDefinition
 */
class UpdateDataDefinition extends BundleUpdateDataDefinition
{
    /**
     * UpdateDataDefinition constructor.
     */
    public function __construct()
    {
        trigger_error(
            sprintf(
                'Use of deprecated class %s. Use instead %s',
                __CLASS__,
                BundleUpdateDataDefinition::class
            ),
            E_USER_DEPRECATED
        );
    }
}
