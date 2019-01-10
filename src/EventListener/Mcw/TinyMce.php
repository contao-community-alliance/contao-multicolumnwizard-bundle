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

use Contao\BackendTemplate;
use MenAtWork\MultiColumnWizardBundle\Event\GetTinyMceStringEvent;

/**
 * Class TinyMce
 */
class TinyMce
{
    /**
     * Listener for building the tiny mce.
     *
     * @param GetTinyMceStringEvent $event
     *
     * @return void
     */
    public function executeEvent(GetTinyMceStringEvent $event)
    {
        $version = $event->getVersion();
        if (version_compare($version, '4.4', '>=')) {
            $this->contao44x($event);
        }
    }

    /**
     * Generate the TinyMce Script.
     *
     * @param GetTinyMceStringEvent $event
     *
     * @return void
     */
    private function contao44x(GetTinyMceStringEvent $event)
    {
        // Get some vars.
        $field = $event->getFieldConfiguration();
        $table = $event->getTableName();
        $id    = $event->getFieldId();

        list ($file, $type) = explode('|', $field['eval']['rte'], 2);

        $fileBrowserTypes = array();
        // Since we don't know if this is the right call for other versions of contao
        // we won't use dependencies injection.
        $pickerBuilder = \System::getContainer()->get('contao.picker.builder');

        foreach (array('file' => 'image', 'link' => 'file') as $context => $fileBrowserType) {
            if ($pickerBuilder->supportsContext($context)) {
                $fileBrowserTypes[] = $fileBrowserType;
            }
        }

        /** @var BackendTemplate|object $objTemplate */
        $objTemplate                   = new BackendTemplate('be_' . $file);
        $objTemplate->selector         = 'ctrl_' . $id;
        $objTemplate->type             = $type;
        $objTemplate->fileBrowserTypes = $fileBrowserTypes;
        $objTemplate->source           = $table . '.' . $id;

        // Deprecated since Contao 4.0, to be removed in Contao 5.0
        $objTemplate->language = \Backend::getTinyMceLanguage();

        $event->setTinyMce($objTemplate->parse());
    }
}
