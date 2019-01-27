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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use MenAtWork\MultiColumnWizardBundle\Event\GetDcaPickerWizardStringEvent;

/**
 * Class Wizard
 */
class DcaPickerWizard
{
    /**
     * Generate the TinyMce Script.
     *
     * @param GetDcaPickerWizardStringEvent $event The event.
     *
     * @return void
     */
    public function executeEvent(GetDcaPickerWizardStringEvent $event)
    {
        // Get some vars.
        $field     = $event->getFieldConfiguration();
        $table     = $event->getTableName();
        $fieldId   = $event->getFieldId();

        // Set the translation.
        if (!isset($GLOBALS['TL_DCA'][$table]['fields'][$fieldId]['label'])) {
            $GLOBALS['TL_DCA'][$table]['fields'][$fieldId]['label'] = $field['label'];
        }

        // Trigger the contao wizard class.
        $string = \Contao\Backend::getDcaPickerWizard(
            $field['eval']['dcaPicker'],
            $table,
            $fieldId,
            $fieldId
        );

        $event->setWizard($string);
    }
}
