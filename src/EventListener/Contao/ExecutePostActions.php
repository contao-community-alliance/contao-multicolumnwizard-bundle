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
 * @author     Julian Aziz Haslinger <me@aziz.wtf>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Contao;

use Contao\Config;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\Input;
use Contao\PageTree;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use FilesModel;
use FileTree;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;
use MenAtWork\MultiColumnWizardBundle\Event\CreateWidgetEvent;
use MenAtWork\MultiColumnWizardBundle\EventListener\BaseListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ExecutePostActions
 */
class ExecutePostActions extends BaseListener
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ExecutePostActions constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();

        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create a new row.
     * Will call the event men-at-work.multi-column-wizard-bundle.create-widget to get the widget.
     *
     * @param string        $action    The action.
     * @param DataContainer $container The current context.
     *
     * @return void
     *
     * @throws ResponseException       For generating the output.
     *
     * @throws BadRequestHttpException Will be thrown if the widget is not from type MCW or the field is unknown.
     */
    public function handleRowCreation($action, $container)
    {
        // Check the context.
        if ('mcwCreateNewRow' != $action) {
            return;
        }

        // Get the field name, handel editAll as well.
        $fieldName = Input::post('name');
        if (!$container instanceof General) {
            $container->inputName = $fieldName;
        }
        if (Input::get('act') == 'editAll') {
            $fieldName = \preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $fieldName);
        }

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $event = new CreateWidgetEvent($container);
        $this->eventDispatcher->dispatch($event::NAME, $event);
        /** @var \Widget $widget */
        $widget = $event->getWidget();

        // Check the instance.
        if (!($widget instanceof MultiColumnWizard)) {
            System::log(
                'Field "' . $fieldName . '" is not a mcw in "' . $container->table . '"',
                __METHOD__,
                TL_ERROR
            );
            throw new BadRequestHttpException('Bad request');
        }

        // The field does not exist
        if (empty($widget)) {
            System::log(
                'Field "' . $fieldName . '" does not exist in definition "' . $container->table . '"',
                __METHOD__,
                TL_ERROR
            );
            throw new BadRequestHttpException('Bad request');
        }

        // Get the max row count or preset it.
        $maxRowCount = Input::post('maxRowId');
        if (empty($maxRowCount)) {
            $maxRowCount = 0;
        }

        throw new ResponseException($this->convertToResponse($widget->generate(($maxRowCount + 1), true)));
    }

    /**
     * Try to rewrite the reload event. We have a tiny huge problem with the field names of the mcw and contao.
     *
     * @param string        $action    The action to execute.
     *
     * @param DataContainer $container The data container.
     *
     * @return void
     *
     * @throws BadRequestHttpException When The field does not exist in the DCA or the requested row could not be found.
     *
     * @throws ResponseException       In all successful cases.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function executePostActions($action, DataContainer $container)
    {
        // Kick out if the context isn't the right one.
        if ($action != 'reloadFiletree_mcw' && $action != 'reloadPagetree_mcw') {
            return;
        }

        $intId    = \Input::get('id');
        $strField = $container->inputName = \Input::post('name');
        // Contao changed the name for FileTree and PageTree widgets
        // @see https://github.com/menatwork/contao-multicolumnwizard-bundle/issues/51
        $vNameCheck = (version_compare(VERSION, '4.4.41', '>=') &&
                       version_compare(VERSION, '4.5.0', '<')) ||
                      version_compare(VERSION, '4.7.7', '>=');

        if ($vNameCheck) {
            $fieldParts       = preg_split('/[\[,]|[]\[,]+/', $strField);
            $container->field = $strField;
            $mcwBaseName      = $fieldParts[0];
            $intRow           = $fieldParts[1];
            $mcwSupFieldName  = $fieldParts[2];
        } else {
            // Get the field name parts.
            $fieldParts = preg_split('/_row[0-9]*_/i', $strField);
            preg_match('/_row[0-9]*_/i', $strField, $arrRow);
            $intRow = substr(substr($arrRow[0], 4), 0, -1);

            // Rebuild field name.
            $container->field = $fieldParts[0] . '[' . $intRow . '][' . $fieldParts[1] . ']';
            $mcwBaseName      = $fieldParts[0];
            $mcwSupFieldName  = $fieldParts[1];
        }

        $mcwId = $mcwBaseName . '_row' . $intRow . '_' . $mcwSupFieldName;

        // Handle the keys in "edit multiple" mode
        if (\Input::get('act') == 'editAll') {
            if ($vNameCheck) {
                $intId       = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $mcwBaseName);
                $mcwBaseName = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $mcwBaseName);
            } else {
                $intId    = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
            }
        }

        // Add the sub configuration into the DCA. We need this for contao. Without it is not possible
        // to get the data for the picker.
        if ($GLOBALS['TL_DCA'][$container->table]['fields'][$mcwBaseName]['inputType'] == 'multiColumnWizard') {
            $GLOBALS['TL_DCA'][$container->table]['fields'][$container->field] =
                $GLOBALS['TL_DCA'][$container->table]['fields'][$mcwBaseName]['eval']['columnFields'][$mcwSupFieldName];

            $GLOBALS['TL_DCA'][$container->table]['fields'][$strField] =
                $GLOBALS['TL_DCA'][$container->table]['fields'][$mcwBaseName]['eval']['columnFields'][$mcwSupFieldName];
        }

        // The field does not exist
        if (!isset($GLOBALS['TL_DCA'][$container->table]['fields'][$strField])) {
            System::log(
                'Field "' . $strField . '" does not exist in DCA "' . $container->table . '"',
                __METHOD__,
                TL_ERROR
            );
            throw new BadRequestHttpException('Bad request');
        }

        $objRow   = null;
        $varValue = null;

        // Load the value
        if (Input::get('act') != 'overrideAll') {
            if ($GLOBALS['TL_DCA'][$container->table]['config']['dataContainer'] == 'File') {
                $varValue = Config::get($strField);
            } elseif ($intId > 0 && Database::getInstance()->tableExists($container->table)) {
                $objRow = Database::getInstance()
                                  ->prepare('SELECT * FROM ' . $container->table . ' WHERE id=?')
                                  ->execute($intId);

                // The record does not exist
                if ($objRow->numRows < 1) {
                    System::log(
                        'A record with the ID "' . $intId . '" does not exist in table "' . $container->table . '"',
                        __METHOD__,
                        TL_ERROR
                    );
                    throw new BadRequestHttpException('Bad request');
                }

                $varValue                = $objRow->$strField;
                $container->activeRecord = $objRow;
            }
        }

        // Call the load_callback
        if (\is_array($GLOBALS['TL_DCA'][$container->table]['fields'][$strField]['load_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$container->table]['fields'][$strField]['load_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $container);
                } elseif (\is_callable($callback)) {
                    $varValue = $callback($varValue, $container);
                }
            }
        }

        // Set the new value
        $varValue = Input::post('value', true);
        $strKey   = (($action == 'reloadPagetree_mcw') ? 'pageTree' : 'fileTree');

        // Convert the selected values
        if ($varValue != '') {
            $varValue = StringUtil::trimsplit("\t", $varValue);

            // Automatically add resources to the DBAFS
            if ($strKey == 'fileTree') {
                foreach ($varValue as $k => $v) {
                    $v = rawurldecode($v);

                    if (Dbafs::shouldBeSynchronized($v)) {
                        $objFile = FilesModel::findByPath($v);

                        if ($objFile === null) {
                            $objFile = Dbafs::addResource($v);
                        }

                        $varValue[$k] = $objFile->uuid;
                    }
                }
            }

            $varValue = serialize($varValue);
        }

        /** @var FileTree|PageTree $strClass */
        $strClass        = $GLOBALS['BE_FFL'][$strKey];
        $fieldAttributes = $strClass::getAttributesFromDca(
            $GLOBALS['TL_DCA'][$container->table]['fields'][$strField],
            $container->inputName,
            $varValue,
            $strField,
            $container->table,
            $container
        );

        $fieldAttributes['id']       = $mcwId;
        $fieldAttributes['name']     = $container->field;
        $fieldAttributes['value']    = $varValue;
        $fieldAttributes['strTable'] = $container->table;
        $fieldAttributes['strField'] = $strField;

        /** @var FileTree|PageTree $objWidget */
        $objWidget = new $strClass($fieldAttributes);
        $strWidget = $objWidget->generate();

        if ($vNameCheck) {
            $strWidget = str_replace(['reloadFiletree', 'reloadFiletreeDMA'], 'reloadFiletree_mcw', $strWidget);
            $strWidget = str_replace(['reloadPagetree', 'reloadPagetreeDMA'], 'reloadPagetree_mcw', $strWidget);
        }

        throw new ResponseException($this->convertToResponse($strWidget));
    }
}
