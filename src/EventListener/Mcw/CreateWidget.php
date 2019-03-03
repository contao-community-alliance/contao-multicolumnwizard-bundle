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

use Contao\Input;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;
use MenAtWork\MultiColumnWizardBundle\Event\CreateWidgetEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CreateWidgetContao
 */
class CreateWidget
{
    /**
     * Create a widget for Contao context.
     *
     * @param CreateWidgetEvent $event The event.
     *
     * @return void
     *
     * @throws BadRequestHttpException When the field does not exist in the DCA.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
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
            System::log(
                'Field "' . $fieldName . '" does not exist in DCA "' . $dcDriver->table . '"',
                __METHOD__,
                TL_ERROR
            );
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

        // Set some more information.
        $widget->currentRecord = $dcDriver->id;
        $widget->activeRecord  = $dcDriver->activeRecord;

        $event->setWidget($widget);
    }

    /**
     * Create a widget for dc-general context.
     *
     * @param CreateWidgetEvent $event The event.
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
