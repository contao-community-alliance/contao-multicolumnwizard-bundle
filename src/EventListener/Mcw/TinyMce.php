<?php

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use Contao\BackendTemplate;
use MenAtWork\MultiColumnWizardBundle\Event\GetTinyMceEvent;

/**
 * Class TinyMce
 */
class TinyMce
{
    /**
     * Listener for building the tiny mce.
     *
     * @param GetTinyMceEvent $event
     *
     * @return void
     */
    public function executeEvent(GetTinyMceEvent $event)
    {
        $version = $event->getVersion();
        if (version_compare($version, '4.4', '>=')) {
            $this->contao44x($event);
        }
    }

    /**
     * Generate the TinyMce Script.
     *
     * @param GetTinyMceEvent $event
     *
     * @return void
     */
    private function contao44x(GetTinyMceEvent $event)
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