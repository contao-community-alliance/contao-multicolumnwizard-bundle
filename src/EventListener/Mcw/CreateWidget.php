<?php

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use Contao\Input;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;
use MenAtWork\MultiColumnWizardBundle\Event\CreateWidgetEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CreateWidgetContao
 *
 * @package MenAtWork\MultiColumnWizardBundle\EventListener\Mcw
 */
class CreateWidget
{
    /**
     * @param CreateWidgetEvent $event
     *
     * @return void
     */
    public function createWidgetContao(CreateWidgetEvent $event)
    {
        /** @var  DcCompat $dcGeneral */
        $dcDriver = $event->getDcDriver();

        // Check the context.
        if (($dcDriver instanceof DcCompat)) {
            return;
        }

        // Get the field name, handel editAll as well.
        $fieldName = $dcDriver->inputName = Input::post('name');
        if (Input::get('act') == 'editAll') {
            $fieldName = \preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $fieldName);
        }
        $dcDriver->field = $fieldName;

        // The field does not exist
        if (!isset($GLOBALS['TL_DCA'][$dcDriver->table]['fields'][$fieldName])) {
            System::log('Field "' . $fieldName . '" does not exist in DCA "' . $dcDriver->table . '"', __METHOD__, TL_ERROR);
            throw new BadRequestHttpException('Bad request');
        }

        /** @var string $widgetClassName */
        $widgetClassName = $GLOBALS['BE_FFL']['multiColumnWizard'];

        /** @var MultiColumnWizard $widget */
        $widget = new $widgetClassName(
            $widgetClassName::getAttributesFromDca(
                $GLOBALS['TL_DCA'][$dcDriver->table]['fields'][$fieldName],
                $dcDriver->inputName,
                '',
                $fieldName,
                $dcDriver->table,
                $dcDriver
            )
        );

        $event->setWidget($widget);
    }

    /**
     * @param CreateWidgetEvent $event
     *
     * @return void
     */
    public function createWidgetDcGeneral(CreateWidgetEvent $event)
    {
        /** @var  DcCompat $dcGeneral */
        $dcGeneral = $event->getDcDriver();

        // Check the context.
        if (!($dcGeneral instanceof DcCompat)) {
            return;
        }

        // Get the field name, handel editAll as well.
        $fieldName = Input::post('name');
        if (Input::get('act') == 'editAll') {
            $fieldName = \preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $fieldName);
        }

        // Trigger the dcg to generate the data.
        $env   = $dcGeneral->getEnvironment();
        $model = $dcGeneral->getModel() ?: $dcGeneral
            ->getEnvironment()
            ->getDataProvider()
            ->getEmptyModel();

        $dcgContaoWidgetManager = new ContaoWidgetManager($env, $model);

        $event->setWidget($dcgContaoWidgetManager->getWidget($fieldName));
    }
}
