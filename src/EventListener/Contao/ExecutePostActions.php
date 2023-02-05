<?php

/**
 * This file is part of menatwork/contao-multicolumnwizard-bundle.
 *
 * (c) 2012-2020 MEN AT WORK.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2020 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Contao;

use Contao\CoreBundle\Monolog\ContaoContext;
use ContaoCommunityAlliance\DcGeneral\ContaoFrontend\View\WidgetManager;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Contao\Config;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\DataContainer;
use Contao\Database;
use Contao\Dbafs;
use Contao\FileTree;
use Contao\FilesModel;
use Contao\Input;
use Contao\PageTree;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Doctrine\ORM\Mapping as ORM;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;
use MenAtWork\MultiColumnWizardBundle\EventListener\BaseListener;
use MenAtWork\MultiColumnWizardBundle\Event\CreateWidgetEvent;
use MenAtWork\MultiColumnWizardBundle\Service\ContaoApiService;
use Monolog\Logger;
use Psr\Log\LogLevel;
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
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var ContaoApiService
     */
    private ContaoApiService $contaoApi;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * ExecutePostActions constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher.
     *
     * @param ContaoApiService         $contaoApi       Bridge to Contao. Replacement for the deprecated functions.
     *
     * @param Logger                   $logger          Logging ;).
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ContaoApiService $contaoApi,
        Logger $logger
    ) {
        parent::__construct();

        $this->contaoApi       = $contaoApi;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
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
        $this->eventDispatcher->dispatch($event, $event::NAME);
        $widget = $event->getWidget();

        // Check the instance.
        if (!($widget instanceof MultiColumnWizard)) {
            $this->logger->log(
                LogLevel::ERROR,
                'Field "' . $fieldName . '" is not a mcw in "' . $container->table . '"',
                [
                    'contao' => new ContaoContext(
                        __CLASS__ . '::' . __FUNCTION__,
                        'MCW Execute Post Action'
                    )
                ]
            );
            throw new BadRequestHttpException('Bad request');
        }

        // The field does not exist
        if (empty($widget)) {
            $this->logger->log(
                LogLevel::ERROR,
                'Field "' . $fieldName . '" does not exist in definition "' . $container->table . '"',
                [
                    'contao' => new ContaoContext(
                        __CLASS__ . '::' . __FUNCTION__,
                        'MCW Execute Post Action'
                    )
                ]
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

        $intId    = Input::get('id');
        $strField = $this->getInputName($container);
        // Contao changed the name for FileTree and PageTree widgets
        // @see https://github.com/menatwork/contao-multicolumnwizard-bundle/issues/51
        $contaoVersion = $this->contaoApi->getContaoVersion();
        $vNameCheck    = (
                version_compare($contaoVersion, '4.4.41', '>=')
                && version_compare($contaoVersion, '4.5.0', '<')
            ) || version_compare($contaoVersion, '4.7.7', '>=');

        $containerField = '';
        if ($vNameCheck) {
            $fieldParts      = preg_split('/[\[,]|[]\[,]+/', $strField);
            $containerField  = $strField;
            $mcwBaseName     = $fieldParts[0];
            $intRow          = $fieldParts[1];
            $mcwSupFieldName = $fieldParts[2];
        } else {
            // Get the field name parts.
            $fieldParts = preg_split('/_row[0-9]*_/i', $strField);
            preg_match('/_row[0-9]*_/i', $strField, $arrRow);
            $intRow = substr(substr($arrRow[0], 4), 0, -1);

            // Rebuild field name.
            $containerField  = $fieldParts[0] . '[' . $intRow . '][' . $fieldParts[1] . ']';
            $mcwBaseName     = $fieldParts[0];
            $mcwSupFieldName = $fieldParts[1];
        }

        if (!($container instanceof DataContainerInterface)) {
            $container->field = $containerField;
        }

        $mcwId = $mcwBaseName . '_row' . $intRow . '_' . $mcwSupFieldName;

        // Handle the keys in "edit multiple" mode
        if (Input::get('act') == 'editAll') {
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
        if (
            ($GLOBALS['TL_DCA'][$container->table]['fields'][$mcwBaseName]['inputType'] == 'multiColumnWizard')
            && !($container instanceof DataContainerInterface)
        ) {
            $widget = MultiColumnWizard::generateSimpleMcw($container->table, $mcwBaseName);
            $fields = $widget->columnFields;

            $GLOBALS['TL_DCA'][$container->table]['fields'][$container->field] = $fields[$mcwSupFieldName];
            $GLOBALS['TL_DCA'][$container->table]['fields'][$strField]         = $fields[$mcwSupFieldName];
        }

        // The field does not exist
        if (
            !(isset($GLOBALS['TL_DCA'][$container->table]['fields'][$strField]))
            && !($container instanceof DataContainerInterface)
        ) {
            $this->logger->log(
                LogLevel::ERROR,
                'Field "' . $strField . '" does not exist in DCA "' . $container->table . '"',
                [
                    'contao' => new ContaoContext(
                        __CLASS__ . '::' . __FUNCTION__,
                        'MCW Execute Post Action'
                    )
                ]
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
                    $this->logger->log(
                        LogLevel::ERROR,
                        'A record with the ID "' . $intId . '" does not exist in table "' . $container->table . '"',
                        [
                            'contao' => new ContaoContext(
                                __CLASS__ . '::' . __FUNCTION__,
                                'MCW Execute Post Action'
                            )
                        ]
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

        /** @var FileTree|PageTree $objWidget */
        $objWidget = $this->buildWidget($container, $strKey, $strField, $mcwId, $varValue);
        $strWidget = $objWidget->generate();

        if ($vNameCheck) {
            $strWidget = str_replace(['reloadFiletree', 'reloadFiletreeDMA'], 'reloadFiletree_mcw', $strWidget);
            $strWidget = str_replace(['reloadPagetree', 'reloadPagetreeDMA'], 'reloadPagetree_mcw', $strWidget);
        }

        throw new ResponseException($this->convertToResponse($strWidget));
    }

    /**
     * Get the input for name for the ajax request.
     *
     * @param DataContainer $container The data container.
     *
     * @return string
     */
    private function getInputName(DataContainer $container): string
    {
        $inputName = Input::post('name');
        if (!($container instanceof DataContainerInterface)) {
            $container->inputName = $inputName;

            return $inputName;
        }

        $reflection = new \ReflectionProperty(DcCompat::class, 'propertyName');
        $reflection->setAccessible(true);
        $reflection->setValue($container, $inputName);

        $this->addDcGeneralProperty($container);

        return $inputName;
    }

    /**
     * Add the property for the dc general.
     *
     * @param DcCompat $container The data container.
     *
     * @return void
     *
     * @throws BadRequestHttpException Throws the exception if the column not exist in property definition.
     */
    private function addDcGeneralProperty(DcCompat $container): void
    {
        $definition = $container->getEnvironment()->getDataDefinition();
        $properties = $definition->getPropertiesDefinition();

        // Convert the property name for find the property in the definition.
        $search       = array('/([\[][0-9]{1,}[\]])/', '/[\[\]]/');
        $replace      = array('__', '');
        $propertyName = \trim(\preg_replace($search, $replace, $container->getPropertyName()), '__');
        if (!$properties->hasProperty($propertyName)) {
            $this->logger->log(
                LogLevel::ERROR,
                \sprintf(
                    'The property "%s" does not exist in the property definition of "%s"',
                    $container->getPropertyName(),
                    $definition->getName()
                ),
                [
                    'contao' => new ContaoContext(
                        __CLASS__ . '::' . __FUNCTION__,
                        'MCW Execute Post Action'
                    )
                ]
            );
            throw new BadRequestHttpException('Bad request');
        }

        $columnProperty = $properties->getProperty($propertyName);
        $propertyClass  = new \ReflectionClass($columnProperty);

        /** @var PropertyInterface $property */
        $property = $propertyClass->newInstance($container->getPropertyName());
        $property
            ->setLabel($columnProperty->getLabel())
            ->setWidgetType($columnProperty->getWidgetType())
            ->setExtra($columnProperty->getExtra())
            ->setDescription($columnProperty->getDescription())
            ->setDefaultValue($columnProperty->getDefaultValue())
            ->setOptions($columnProperty->getOptions());
        $properties->addProperty($property);
    }

    /**
     * Build the widget.
     *
     * @param DataContainer $container    The data container.
     * @param string        $formFieldKey The backend form field key.
     * @param string        $propertyName The property name.
     * @param string        $mcwId        The mcw id.
     * @param mixed         $value        The property value.
     *
     * @return Widget|FileTree|PageTree
     */
    private function buildWidget(
        DataContainer $container,
        string $formFieldKey,
        string $propertyName,
        string $mcwId,
        $value
    ) {
        if ($container instanceof DataContainerInterface) {
            return $this->buildWidgetForDcg($container, $value);
        }

        return $this->buildWidgetNonDcg($container, $formFieldKey, $propertyName, $mcwId, $value);
    }

    /**
     * Build the widget for the dc general.
     *
     * @param DataContainerInterface|DcCompat $container The data container.
     * @param mixed                           $value     The property value.
     *
     * @return Widget|FileTree|PageTree
     *
     * @throws DcGeneralRuntimeException Throws if a property does not exist in the property definition.
     */
    private function buildWidgetForDcg(DataContainerInterface $container, $value): Widget
    {
        $environment  = $container->getEnvironment();
        $definition   = $environment->getDataDefinition();
        $properties   = $definition->getPropertiesDefinition();
        $dataProvider = $environment->getDataProvider();

        if (!$properties->hasProperty($container->getPropertyName())) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'The property "%s" does not exist in the property definition of "%s"',
                    $container->getPropertyName(),
                    $definition->getName()
                )
            );
        }
        $property = $properties->getProperty($container->getPropertyName());

        $model = $dataProvider->getEmptyModel();
        $model->setId('mcw_' . $container->getPropertyName());
        $model->setProperty($property->getName(), $value);

        if ($this->contaoApi->isFrontend()) {
            $manager = new WidgetManager($environment, $model);
        } else {
            $manager = new ContaoWidgetManager($environment, $model);
        }

        $widget = $manager->getWidget($property->getName());

        $properties->removeProperty($property);

        return $widget;
    }

    /**
     * Build the widget for the legacy data container.
     *
     * @param DataContainer $container    The data container.
     * @param string        $formFieldKey The backend form field key.
     * @param string        $propertyName The property name.
     * @param string        $mcwId        The mcw id.
     * @param mixed         $value        The property value.
     *
     * @return Widget|FileTree|PageTree
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function buildWidgetNonDcg(
        DataContainer $container,
        string $formFieldKey,
        string $propertyName,
        string $mcwId,
        $value
    ): Widget {
        /** @var FileTree|PageTree $widgetClass */
        $widgetClass     = $GLOBALS['BE_FFL'][$formFieldKey];
        $fieldAttributes = $widgetClass::getAttributesFromDca(
            $GLOBALS['TL_DCA'][$container->table]['fields'][$propertyName],
            $container->inputName,
            $value,
            $propertyName,
            $container->table,
            $container
        );

        $fieldAttributes['id']       = $mcwId;
        $fieldAttributes['name']     = $container->field;
        $fieldAttributes['value']    = $value;
        $fieldAttributes['strTable'] = $container->table;
        $fieldAttributes['strField'] = $formFieldKey;

        /** @var FileTree|PageTree $widget */
        $widget = new $widgetClass($fieldAttributes);

        return $widget;
    }
}
