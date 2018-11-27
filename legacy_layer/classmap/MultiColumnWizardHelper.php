<?php

use Contao\DataContainer;
use Contao\Template;
use MenAtWork\MultiColumnWizardBundle\Helper\MultiColumnWizardHelper as BundleMultiColumnWizardHelper;

/**
 * Class MultiColumnWizardHelper
 *
 * @package    MultiColumnWizard
 *
 * @deprecated Deprecated and will be removed in version 4. Use
 *             MenAtWork\MultiColumnWizardBundle\Helper\MultiColumnWizardHelper or the services.
 */
class MultiColumnWizardHelper extends BundleMultiColumnWizardHelper
{
    /**
     * Just here to make the constructor public.
     */
    public function __construct()
    {
        trigger_error(
            sprintf(
                'Use of deprecated class %s. Use instead %s',
                __CLASS__,
                BundleMultiColumnWizardHelper::class
            )
        );

        parent::__construct();
    }

    /**
     * @param Template $objTemplate
     *
     * @return void
     *
     * @deprecated Use the service maw.mcw.events.listener.parse_template
     */
    public function addScriptsAndStyles(Template &$objTemplate)
    {
        $serviceName = 'maw.mcw.events.listener.parse_template';
        trigger_error(
            sprintf(
                'Use of deprecated function %s::%s. Use instead the service %s::%s',
                __CLASS__,
                __FUNCTION__,
                $serviceName,
                __FUNCTION__
            )
        );

        /** @var \MenAtWork\MultiColumnWizardBundle\Contao\Events\ParseTemplate $helper */
        $helper = \System::getContainer()->get($serviceName);
        $helper->addScriptsAndStyles($objTemplate);
    }

    /**
     * @param $strTable
     *
     * @return void
     *
     * @deprecated Use the service maw.mcw.events.listener.load_data_container
     */
    public function supportModalSelector($strTable)
    {
        $serviceName = 'maw.mcw.events.listener.load_data_container';
        trigger_error(
            sprintf(
                'Use of deprecated function %s::%s. Use instead the service %s::%s',
                __CLASS__,
                __FUNCTION__,
                $serviceName,
                __FUNCTION__
            )
        );

        /** @var \MenAtWork\MultiColumnWizardBundle\Contao\Events\LoadDataContainer $helper */
        $helper = \System::getContainer()->get($serviceName);
        $helper->supportModalSelector($strTable);
    }

    /**
     * @return void
     *
     * @deprecated Use the maw.mcw.events.listener.initialize_system
     */
    public function changeAjaxPostActions()
    {
        $serviceName = 'maw.mcw.events.listener.initialize_system';
        trigger_error(
            sprintf(
                'Use of deprecated function %s::%s. Use instead the service %s::%s',
                __CLASS__,
                __FUNCTION__,
                $serviceName,
                __FUNCTION__
            )
        );

        /** @var \MenAtWork\MultiColumnWizardBundle\Contao\Events\InitializeSystem $helper */
        $helper = \System::getContainer()->get($serviceName);
        $helper->changeAjaxPostActions();
    }

    /**
     * @param                       $action
     *
     * @param DataContainer         $dc
     *
     * @return void
     *
     * @deprecated Use the maw.mcw.events.listener.execute_post_actions
     */
    public function executePostActions($action, DataContainer $dc)
    {
        $serviceName = 'maw.mcw.events.listener.execute_post_actions';
        trigger_error(
            sprintf(
                'Use of deprecated function %s::%s. Use instead the service %s::%s',
                __CLASS__,
                __FUNCTION__,
                $serviceName,
                __FUNCTION__
            )
        );

        /** @var \MenAtWork\MultiColumnWizardBundle\Contao\Events\ExecutePostActions $helper */
        $helper = \System::getContainer()->get($serviceName);
        $helper->executePostActions($action, $dc);
    }

    /**
     * @param DataContainer $dc
     *
     * @return string
     *
     * @deprecated Use the \MenAtWork\MultiColumnWizardBundle\Helper\MultiColumnWizardHelper::mcwFilePicker
     */
    public function mcwFilePicker(DataContainer $dc)
    {
        return parent::mcwFilePicker($dc);
    }
}
