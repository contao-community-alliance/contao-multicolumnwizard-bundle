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

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\System;
use MenAtWork\MultiColumnWizardBundle\Event\GetTinyMceStringEvent;

/**
 * Class TinyMce
 */
class TinyMce
{
    /**
     * Generate the TinyMce Script.
     *
     * @param GetTinyMceStringEvent $event The event.
     *
     * @return void
     */
    public function executeEvent(GetTinyMceStringEvent $event)
    {
        // Get some vars.
        $field   = $event->getFieldConfiguration();
        $table   = $event->getTableName();
        $fieldId = $event->getFieldId();

        list ($file, $type) = explode('|', $field['eval']['rte'], 2);

        $fileBrowserTypes = array();
        // Since we don't know if this is the right call for other versions of contao
        // we won't use dependencies injection.
        $pickerBuilder = System::getContainer()->get('contao.picker.builder');

        foreach (array('file' => 'image', 'link' => 'file') as $context => $fileBrowserType) {
            if ($pickerBuilder->supportsContext($context)) {
                $fileBrowserTypes[] = $fileBrowserType;
            }
        }

        /** @var BackendTemplate|object $objTemplate */
        $objTemplate                   = new BackendTemplate('be_' . $file);
        $objTemplate->selector         = 'ctrl_' . $fieldId;
        $objTemplate->type             = $type;
        $objTemplate->fileBrowserTypes = $fileBrowserTypes;
        $objTemplate->source           = $table . '.' . $fieldId;

        // Deprecated since Contao 4.0, to be removed in Contao 5.0
        $objTemplate->language = Backend::getTinyMceLanguage();

        $event->setTinyMce($objTemplate->parse());
    }
}
