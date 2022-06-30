<?php

/**
 * This file is part of menatwork/contao-multicolumnwizard-bundle.
 *
 * (c) 2012-2022 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/contao-multicolumnwizard-bundle
 * @author     Andreas Burg <info@andreasburg.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     André <simmert@users.noreply.github.com>
 * @author     Antoine DAVID <adavid@addictic.fr>
 * @author     Benny Born <benny@bennyborn.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christoph Wiechert <wio@psitrax.de>
 * @author     civ23 <kai@kapalla.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Dominik Tomasi <d.tomasi@upcom.ch>
 * @author     Gerald Meier <garyee@gmx.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Jozef Dvorský <creatingo@users.noreply.github.com>
 * @author     Julian Aziz Haslinger <me@aziz.wtf>
 * @author     Kester Mielke <kester.mieke@gmx.net>
 * @author     mediabakery <s.tilch@mediabakery.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Stefan Lindecke <github.com@chektrion.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     w3scout <info@w3scouts.com>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Andreas Dziemba <adziemba@web.de>
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @author     doishub <daniele@oveleon.de>
 * @author     info@e-spin.de <info@e-spin.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2022 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Contao\Widgets;

use Contao\BackendTemplate;
use Contao\DataContainer;
use Contao\Date;
use Contao\DC_File;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MenAtWork\MultiColumnWizardBundle\Event\GetColorPickerStringEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetDatePickerStringEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetTinyMceStringEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetDcaPickerWizardStringEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnWizard
 *
 * @property string columnTemplate The custom template.
 * @property array  columnFields   List fo fields.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MultiColumnWizard extends Widget
{
    /**
     * The event Dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Submit user input.
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Value.
     *
     * @var mixed
     */
    protected $varValue = array();

    /**
     * Widget errors to store.
     *
     * @var array
     */
    protected $arrWidgetErrors = array();

    /**
     * Callback data.
     *
     * @var array
     */
    protected $arrCallback = false;

    /**
     * Min count.
     *
     * @var int
     */
    protected $minCount = 0;

    /**
     * Max count.
     *
     * @var int
     */
    protected $maxCount = 0;

    /**
     * Tableless.
     *
     * @var boolean
     */
    protected $blnTableless = false;

    /**
     * Row specific data.
     *
     * @var array
     */
    protected $arrRowSpecificData = array();

    /**
     * Buttons.
     *
     * @var array
     */
    protected $arrButtons = [
        'new'    => 'new.gif',
        'delete' => 'delete.gif',
        'move'   => 'drag.gif'
    ];

    /**
     * Initialize the object
     *
     * @param bool $arrAttributes The attributes for the widget.
     */
    public function __construct($arrAttributes = false)
    {
        // Ensure we have aliased the deprecated class - circumvent issue #39 but can not trigger deprecation then. :/
        if (!class_exists('MultiColumnWizard', false)) {
            class_alias(self::class, 'MultiColumnWizard');
        }

        parent::__construct($arrAttributes);

        if (TL_MODE == 'FE') {
            $this->strTemplate = 'form_widget';
            $this->loadDataContainer($arrAttributes['strTable']);
        }

        $this->eventDispatcher = System::getContainer()->get('event_dispatcher');

        /*
         * Load the callback data if there's any
         * (do not do this in __set() already because then we don't have access to currentRecord)
         */

        if (is_array($this->arrCallback)) {
            $this->import($this->arrCallback[0]);
            $this->columnFields = $this->{$this->arrCallback[0]}->{$this->arrCallback[1]}($this);
        }
    }

    /**
     * Add specific attributes
     *
     * @param string $strKey   The key to search for.
     *
     * @param mixed  $varValue The value to set.
     *
     * @return void
     *
     * @throws \Exception Throws a exception if something went wrong.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'value':
                $this->varValue = StringUtil::deserialize($varValue, true);

                /*
                 * reformat array if we have only one field
                 * from array[] = value
                 * to array[]['fieldname'] = value
                 */

                if ($this->flatArray) {
                    $arrNew = array();

                    foreach ($this->varValue as $val) {
                        $arrNew[] = array(key($this->columnFields) => $val);
                    }

                    $this->varValue = $arrNew;
                }
                break;

            case 'mandatory':
                $this->arrConfiguration['mandatory'] = $varValue ? true : false;
                break;

            case 'columnsCallback':
                if (!is_array($varValue)) {
                    throw new \Exception('Parameter "columns" has to be an array: array(\'Class\', \'Method\')!');
                }

                $this->arrCallback = $varValue;
                break;

            case 'buttons':
                if (is_array($varValue)) {
                    $this->arrButtons = array_merge($this->arrButtons, $varValue);
                }
                break;

            case 'hideButtons':
                if ($varValue === true) {
                    $this->arrButtons = array();
                }
                // No break here.
            case 'disableSorting':
                if ($varValue == true) {
                    unset($this->arrButtons['up']);
                    unset($this->arrButtons['down']);
                    unset($this->arrButtons['move']);
                }
                break;

            case 'dragAndDrop':
                if ($varValue === false) {
                    unset($this->arrButtons['move']);
                    unset($this->arrButtons['delete']);
                    $this->arrButtons['up']     = 'up.gif';
                    $this->arrButtons['down']   = 'down.gif';
                    $this->arrButtons['delete'] = 'delete.gif';
                }
                break;

            case 'minCount':
                $this->minCount = $varValue;
                break;

            case 'maxCount':
                $this->maxCount = $varValue;
                break;

            case 'generateTableless':
                $this->blnTableless = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Get specific attributes
     *
     * @param string $strKey The key to get.
     *
     * @return array|string
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'value':
                /*
                 * reformat array if we have only one field
                 * from array[]['fieldname'] = value
                 * to array[] = value
                 * so we have the same behavoir like multiple-checkbox fields
                 */

                if ($this->flatArray) {
                    $arrNew = array();

                    foreach ($this->varValue as $val) {
                        $arrNew[] = $val[key($this->columnFields)];
                    }

                    return $arrNew;
                } else {
                    return parent::__get($strKey);
                }
                break;

            default:
                return parent::__get($strKey);
        }
    }

    /**
     * Helper function, which will init the mcw with a minimal setting.
     *
     * @param string $table The name of the table.
     *
     * @param string $field The name of the field.
     *
     * @return self
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function generateSimpleMcw($table, $field)
    {
        if (!isset($GLOBALS['TL_DCA'][$table]['fields'][$field])) {
            return null;
        }

        $dcaData = $GLOBALS['TL_DCA'][$table]['fields'][$field];

        return new self(
            self::getAttributesFromDca(
                $dcaData,
                $field,
                null,
                $field,
                $table,
                null
            )
        );
    }

    /**
     * Generate the label and return it as string
     *
     * @return string The label markup
     */
    public function generateLabel()
    {
        foreach ($this->columnFields as $arrField) {
            if (isset($arrField['eval']['mandatory']) && $arrField['eval']['mandatory']) {
                $this->addAttribute('mandatory', true);
                break;
            }
        }

        return parent::generateLabel();
    }

    /**
     * Trigger the event men-at-work.multi-column-wizard-bundle.get-tiny-mce
     * Try to get help for generating the TinyMceScript.
     *
     * @param string $fieldId            The id of the field.
     *
     * @param array  $fieldConfiguration The filed configuration.
     *
     * @param string $tableName          The name of the table.
     *
     * @return string
     */
    protected function getMcWTinyMCEString($fieldId, $fieldConfiguration, $tableName)
    {
        // Final check if we have the right context.
        if (empty($fieldConfiguration['eval']['rte'])) {
            return '';
        }

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $event = new GetTinyMceStringEvent(
            $fieldId,
            $tableName,
            $fieldConfiguration
        );
        $this->eventDispatcher->dispatch($event, $event::NAME);

        // Return the result.
        return $event->getTinyMce();
    }

    /**
     * Trigger the event men-at-work.multi-column-wizard-bundle.get-date-picker
     *
     * @param string $fieldId            The id of the field.
     *
     * @param string $fieldName          The name of the column/field.
     *
     * @param string $rgxp               Never used. But needed for API.
     *
     * @param array  $fieldConfiguration The filed configuration.
     *
     * @param string $tableName          The name of the table.
     *
     * @return string
     */
    protected function getMcWDatePickerString(
        $fieldId,
        $fieldName,
        $rgxp = null,
        $fieldConfiguration = null,
        $tableName = null
    ) {
        // Datepicker
        if (
            (
                !isset($fieldConfiguration['eval']['datepicker'])
                || $fieldConfiguration['eval']['datepicker'] != true
            )
            || !isset($fieldConfiguration['eval']['rgxp'])
        ) {
            return '';
        }

        // Add a warning if some one use the old rgxp parameter.
        if (!empty($rgxp)) {
            trigger_error(
                sprintf(
                    'Use of deprecated parameter for %s::%s - %s. 
                    Use instead the $fieldConfiguration and $fieldConfiguration[\'eval\'][\'rgxp\'] for this.',
                    __CLASS__,
                    __FUNCTION__,
                    $rgxp
                ),
                E_USER_DEPRECATED
            );
        }

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $event = new GetDatePickerStringEvent(
            $fieldId,
            $tableName,
            $fieldConfiguration,
            $fieldName,
            $fieldConfiguration['eval']['rgxp']
        );
        $this->eventDispatcher->dispatch($event, $event::NAME);

        // Return the result.
        return $event->getDatePicker();
    }

    /**
     * Trigger the event men-at-work.multi-column-wizard-bundle.get-color-picker
     *
     * @param string $fieldId            The id of the field.
     *
     * @param string $fieldName          The name of the field.
     *
     * @param array  $fieldConfiguration The filed configuration.
     *
     * @param string $tableName          The name of the table.
     *
     * @return string
     */
    protected function getMcWColorPicker(
        $fieldId,
        $fieldName,
        $fieldConfiguration = null,
        $tableName = null
    ) {
        // Check if we have an configuration.
        if (!isset($fieldConfiguration['eval']['colorpicker'])) {
            return '';
        }

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $event = new GetColorPickerStringEvent(
            $fieldId,
            $tableName,
            $fieldConfiguration,
            $fieldName
        );
        $this->eventDispatcher->dispatch($event, $event::NAME);

        // Return the result.
        return $event->getColorPicker();
    }

    /**
     * Trigger the event men-at-work.multi-column-wizard-bundle.get-dca-picker-wizard
     *
     * @param string $fieldId            The id of the field.
     *
     * @param string $fieldName          The name of the field.
     *
     * @param array  $fieldConfiguration The filed configuration.
     *
     * @param string $tableName          The name of the table.
     *
     * @return string
     */
    protected function getMcWDcaPickerWizard(
        $fieldId,
        $fieldName,
        $fieldConfiguration = null,
        $tableName = null
    ) {
        // Check if we have an configuration.
        if (
            !isset($fieldConfiguration['eval']['dcaPicker'])
            || (
                !\is_array($fieldConfiguration['eval']['dcaPicker'])
                && !$fieldConfiguration['eval']['dcaPicker'] === true
            )
        ) {
            return '';
        }

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $event = new GetDcaPickerWizardStringEvent(
            $fieldId,
            $tableName,
            $fieldConfiguration,
            $fieldName
        );
        $this->eventDispatcher->dispatch($event, $event::NAME);

        // Return the result.
        return $event->getWizard();
    }

    /**
     * Try to get the DC Drive.
     * For the DCG we have to handel the HTTP_X_REQUESTED_WITH, to cancel an endless loop.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getDcDriver()
    {
        if (method_exists(DataContainer::class, 'getDriverForTable')) {
            $dataContainer = DataContainer::getDriverForTable($this->strTable);
        } else {
            $dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'];
        }

        if ($dataContainer == \DC_General::class) {
            $dcgXRequestTemp                  = $_SERVER['HTTP_X_REQUESTED_WITH'];
            $_SERVER['HTTP_X_REQUESTED_WITH'] = null;
        }

        $dcDriver = new $dataContainer($this->strTable);

        if ($dataContainer == \DC_General::class) {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = $dcgXRequestTemp;
        }

        return $dcDriver;
    }

    /**
     * Validate the data and rebuild the data.
     *
     * @param mixed $varInput The Input to check and manipulate.
     *
     * @return array The new array with the valid data.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function validator($varInput)
    {
        // Cast all to an array. This should prevent us for some errors. See #35
        $varInput = (array) $varInput;

        // The order of the data are in the right order. So just catch it and save it.
        $sortOrder = [];
        $sortId    = 0;
        foreach (\array_keys($varInput) as $id) {
            $sortOrder[$sortId] = $id;
            $sortId++;
        }

        $blnHasError   = false;
        $varInputCount = count($varInput);
        for ($i = 0; $i < $varInputCount; $i++) {
            $this->activeRow = $i;

            if (!$this->columnFields) {
                continue;
            }

            // Walk every column
            foreach ($this->columnFields as $strKey => $arrField) {
                $objWidget = $this->initializeWidget($arrField, $i, $strKey, $varInput[$i][$strKey]);

                // can be null on error, or a string on input_field_callback
                if (!is_object($objWidget)) {
                    continue;
                }

                // Hack for Checkboxes.
                if ($arrField['inputType'] == 'checkbox' && isset($varInput[$i][$strKey])) {
                    Input::setPost($objWidget->name, $varInput[$i][$strKey]);
                }

                $objWidget->validate();

                $varValue = $objWidget->value;

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                $rgxp = ($arrField['eval']['rgxp'] ?? '');
                if (
                    !$objWidget->hasErrors()
                    && ($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim')
                    && $varValue != ''
                ) {
                    $objDate  = new Date($varValue, $this->getNumericDateFormat($rgxp));
                    $varValue = $objDate->tstamp;
                }

                // Save callback
                if (isset($arrField['save_callback']) && is_array($arrField['save_callback'])) {
                    foreach ($arrField['save_callback'] as $callback) {
                        $this->import($callback[0]);

                        try {
                            $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
                        } catch (\Exception $exception) {
                            $objWidget->class = 'error';
                            $objWidget->addError($exception->getMessage());
                        }
                    }
                }

                // Convert binary UUIDs for DC_File driver (see contao#6893)
                if (
                    $arrField['inputType'] == 'fileTree'
                    && 'DC_' . $GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'] === DC_File::class
                ) {
                    $varValue = StringUtil::deserialize($varValue);

                    if (!\is_array($varValue)) {
                        $varValue = StringUtil::binToUuid($varValue);
                    } else {
                        $varValue = serialize(array_map('StringUtil::binToUuid', $varValue));
                    }
                }

                $varInput[$i][$strKey] = $varValue;

                // Do not submit if there are errors
                if ($objWidget->hasErrors()) {
                    // store the errors
                    $this->arrWidgetErrors[$strKey][$i] = $objWidget->getErrors();

                    $blnHasError = Input::post('SUBMIT_TYPE') != 'auto';
                }
            }
        }

        if ($this->minCount > 0 && count($varInput) < $this->minCount) {
            $this->blnSubmitInput = false;
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mcwMinCount'], $this->minCount));
        }

        if ($this->maxCount > 0 && count($varInput) > $this->maxCount) {
            $this->blnSubmitInput = false;
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mcwMaxCount'], $this->maxCount));
        }

        if ($blnHasError) {
            $this->blnSubmitInput = false;
            $this->addError($GLOBALS['TL_LANG']['ERR']['general']);
        }

        // Rebuild the order.
        $sortedData = [];
        foreach ($sortOrder as $sortId => $dataId) {
            $sortedData[$sortId] = $varInput[$dataId];
        }

        // Rebuild the errors.
        $dataIdToSortId = \array_flip($sortOrder);
        $newWidgetError = [];
        foreach (\array_keys($this->arrWidgetErrors) as $field) {
            foreach ($this->arrWidgetErrors[$field] as $rowId => $fieldErrors) {
                $sortId                          = $dataIdToSortId[$rowId];
                $newWidgetError[$field][$sortId] = $fieldErrors;
            }
        }
        $this->arrWidgetErrors = $newWidgetError;


        return $sortedData;
    }


    /**
     * Generate the widget and return it as string
     *
     * @param null|int $overwriteRowCurrentRow Overwrite the row count. This will generate only one row with the given
     *                                         id.
     *
     * @param bool     $onlyRows               If true, only row's will be output.
     *
     * @return string The HTML code of the widget.
     *
     * @throws \Exception If something went wrong.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function generate($overwriteRowCurrentRow = null, $onlyRows = false)
    {
        $this->strCommand = 'cmd_' . $this->strField;
        $arrUnique        = array();
        $arrDatepicker    = array();
        $arrColorpicker   = array();
        $arrTinyMCE       = array();

        foreach ($this->columnFields as $strKey => $arrField) {
            // Store unique fields
            if (isset($arrField['eval']['unique']) && $arrField['eval']['unique']) {
                $arrUnique[] = $strKey;
            }

            // Store date picker fields
            if (isset($arrField['eval']['datepicker']) && $arrField['eval']['datepicker']) {
                $arrDatepicker[] = $strKey;
            }

            // Store color picker fields
            if (isset($arrField['eval']['colorpicker']) && $arrField['eval']['colorpicker']) {
                $arrColorpicker[] = $strKey;
            }

            // Store tiny mce fields
            if (
                isset($arrField['eval']['rte']) && $arrField['eval']['rte']
                && strncmp($arrField['eval']['rte'], 'tiny', 4) === 0
            ) {
                foreach ($this->varValue as $row => $value) {
                    $tinyId = 'ctrl_' . $this->strField . '_row' . $row . '_' . $strKey;

                    $GLOBALS['TL_RTE']['tinyMCE'][$tinyId] = array(
                        'id'   => $tinyId,
                        'file' => 'tinyMCE',
                        'type' => null
                    );
                }

                $arrTinyMCE[] = $strKey;
            }

            if ($arrField['inputType'] == 'hidden') {
                continue;
            }
        }

        $intNumberOfRows = max(count($this->varValue), 1);

        // always show the minimum number of rows if set
        if ($this->minCount && ($intNumberOfRows < $this->minCount)) {
            $intNumberOfRows = $this->minCount;
        }

        $arrItems        = array();
        $arrHiddenHeader = array();

        if ($overwriteRowCurrentRow !== null) {
            $i               = \intval($overwriteRowCurrentRow);
            $intNumberOfRows = ($i + 1);
        } else {
            $i = 0;
        }

        // Add input fields
        for (; $i < $intNumberOfRows; $i++) {
            $this->activeRow = $i;
            $strHidden       = '';

            // Walk every column
            foreach ($this->columnFields as $strKey => $arrField) {
                $strWidget     = '';
                $blnHiddenBody = false;

                if (isset($arrField['eval']['hideHead']) && $arrField['eval']['hideHead'] == true) {
                    $arrHiddenHeader[$strKey] = true;
                }

                // load row specific data (useful for example for default values in different rows)
                if (isset($this->arrRowSpecificData[$i][$strKey])) {
                    $arrField = array_merge($arrField, $this->arrRowSpecificData[$i][$strKey]);
                }

                $objWidget = $this->initializeWidget(
                    $arrField,
                    $i,
                    $strKey,
                    ($this->varValue[$i][$strKey] ?? null)
                );

                // load errors if there are any
                if (!empty($this->arrWidgetErrors[$strKey][$i])) {
                    foreach ($this->arrWidgetErrors[$strKey][$i] as $strErrorMsg) {
                        $objWidget->addError($strErrorMsg);
                    }
                }

                if ((null !== $objWidget) && isset($arrField['wizard'])) {
                    $wizard = '';

                    $dc               = $this->getDcDriver();
                    $dc->field        = $strKey;
                    $dc->inputName    = $objWidget->id;
                    $dc->strInputName = $objWidget->id;
                    $dc->value        = $objWidget->value;

                    if (is_array($arrField['wizard'])) {
                        foreach ($arrField['wizard'] as $callback) {
                            $this->import($callback[0]);
                            $wizard .= $this->{$callback[0]}->{$callback[1]}($dc, $objWidget);
                        }
                    } elseif (is_callable($arrField['wizard'])) {
                        $wizard .= $arrField['wizard']($dc, $objWidget);
                    }

                    $objWidget->wizard = $wizard;
                }

                if ($objWidget === null) {
                    continue;
                } elseif (is_string($objWidget)) {
                    $strWidget = $objWidget;
                } elseif ($arrField['inputType'] == 'hidden') {
                    $strHidden .= $objWidget->generate();
                    continue;
                } elseif (
                    (isset($arrField['eval']['hideBody']) && $arrField['eval']['hideBody'] == true)
                    || (isset($arrField['eval']['hideHead']) && $arrField['eval']['hideHead'] == true)
                ) {
                    if (($arrField['eval']['hideBody'] ?? false) == true) {
                        $blnHiddenBody = true;
                    }

                    $strWidget = $objWidget->parse();
                } else {
                    $additionalCode = [];

                    // Date picker.
                    $additionalCode['datePicker'] = $this->getMcWDatePickerString(
                        $objWidget->id,
                        $strKey,
                        null,
                        $arrField,
                        $this->strTable
                    );

                    // Color picker.
                    $additionalCode['colorPicker'] = $this->getMcWColorPicker(
                        $objWidget->id,
                        $strKey,
                        $arrField,
                        $this->strTable
                    );

                    // Tiny MCE.
                    if (
                        isset($arrField['eval']['rte'])
                        && $arrField['eval']['rte']
                        && strncmp($arrField['eval']['rte'], 'tiny', 4) === 0
                    ) {
                        $additionalCode['tinyMce'] = $this->getMcWTinyMCEString(
                            $objWidget->id,
                            $arrField,
                            $this->strTable
                        );

                        $arrField['eval']['tl_class'] .= ' tinymce';
                    }

                    // Add custom wizard
                    $additionalCode['dcaPicker'] = $this->getMcWDcaPickerWizard(
                        $objWidget->id,
                        $strKey,
                        $arrField,
                        $this->strTable
                    );

                    // Remove empty elements.
                    $additionalCode = array_filter(
                        $additionalCode,
                        function ($value) {
                            return !empty($value);
                        }
                    );

                    $strWidget = sprintf(
                        '%s%s',
                        $objWidget->parse(),
                        implode('', $additionalCode)
                    );
                }

                // Contao changed the name for FileTree and PageTree widgets
                // @see https://github.com/menatwork/contao-multicolumnwizard-bundle/issues/51
                $contaoVersion = VERSION . '.' . BUILD;

                if (
                    (
                        version_compare($contaoVersion, '4.4.41', '>=')
                        && version_compare($contaoVersion, '4.5.0', '<')
                    )
                    || version_compare($contaoVersion, '4.7.7', '>=')
                ) {
                    $strWidget = str_replace(['reloadFiletree', 'reloadFiletreeDMA'], 'reloadFiletree_mcw', $strWidget);
                    $strWidget = str_replace(['reloadPagetree', 'reloadPagetreeDMA'], 'reloadPagetree_mcw', $strWidget);
                }

                // Build array of items
                if (isset($arrField['eval']['columnPos']) && $arrField['eval']['columnPos'] != '') {
                    $arrItems[$i][$objWidget->columnPos]['entry']   .= $strWidget;
                    $arrItems[$i][$objWidget->columnPos]['valign']   = $arrField['eval']['valign'];
                    $arrItems[$i][$objWidget->columnPos]['tl_class'] = $arrField['eval']['tl_class'];
                    $arrItems[$i][$objWidget->columnPos]['hide']     = $blnHiddenBody;
                } else {
                    $arrItems[$i][$strKey] = array
                    (
                        'entry'    => $strWidget,
                        'valign'   => $arrField['eval']['valign'] ?? null,
                        'tl_class' => $arrField['eval']['tl_class'],
                        'hide'     => $blnHiddenBody
                    );
                }
            }
        }

        if ($this->blnTableless) {
            $strOutput = $this->generateDiv(
                $arrUnique,
                $arrDatepicker,
                $arrColorpicker,
                $strHidden,
                $arrItems,
                $arrHiddenHeader,
                $onlyRows
            );
        } elseif ($this->columnTemplate != '') {
            $strOutput = $this->generateTemplateOutput(
                $arrUnique,
                $arrDatepicker,
                $arrColorpicker,
                $strHidden,
                $arrItems,
                $arrHiddenHeader,
                $onlyRows
            );
        } else {
            $strOutput = $this->generateTable(
                $arrUnique,
                $arrDatepicker,
                $arrColorpicker,
                $strHidden,
                $arrItems,
                $arrHiddenHeader,
                $onlyRows
            );
        }

        return $strOutput;
    }

    /**
     * Initialize widget
     *
     * Based on DataContainer::row() from Contao 2.10.1
     *
     * @param array  $arrField Field configuration.
     *
     * @param int    $intRow   Number of the current row.
     *
     * @param string $strKey   Key of the filed.
     *
     * @param mixed  $varValue The value of the field.
     *
     * @return Widget|null The widget or null.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function initializeWidget(&$arrField, $intRow, $strKey, $varValue)
    {
        $xlabel          = '';
        $strContaoPrefix = 'contao/';

        // YACE support for leo unglaub :)
        if (defined('YACE')) {
            $strContaoPrefix = '';
        }

        // pass activeRecord to widget
        $arrField['activeRecord'] = $this->activeRecord;

        // Toggle line wrap (textarea)
        if ($arrField['inputType'] == 'textarea' && $arrField['eval']['rte'] == '') {
            $xlabel .= ' '
                . Image::getHtml(
                    'wrap.gif',
                    $GLOBALS['TL_LANG']['MSC']['wordWrap'],
                    'title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['wordWrap'])
                    . '" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_'
                    . $this->strId
                    . '_row'
                    . $intRow
                    . '_'
                    . $strKey
                    . '\');"'
                );
        }

        // Add the help wizard
        if (isset($arrField['eval']['helpwizard']) && $arrField['eval']['helpwizard']) {
            $xlabel .= ' <a href="contao/help.php?table=' . $this->strTable . '&amp;field=' . $this->strField
                . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['helpWizard'])
                . '" onclick="Backend.openModalIframe({\'width\':735,\'height\':405,\'title\':\''
                . StringUtil::specialchars(str_replace(
                    "'",
                    "\\'",
                    $arrField['label'][0]
                ))
                . '\',\'url\':this.href});return false">'
                . Image::getHtml(
                    'about.gif',
                    $GLOBALS['TL_LANG']['MSC']['helpWizard'],
                    'style="vertical-align:text-bottom"'
                ) . '</a>';
        }

        // Add the popup file manager
        if ($arrField['inputType'] == 'fileTree' || $arrField['inputType'] == 'pageTree') {
            $path = '';

            if (isset($arrField['eval']['path'])) {
                $path = '?node=' . $arrField['eval']['path'];
            }

            $xlabel              .= ' <a href="'
                . $strContaoPrefix . 'files.php' . $path
                . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['fileManager'])
                . '" data-lightbox="files 765 80%">'
                . Image::getHtml(
                    'filemanager.gif',
                    $GLOBALS['TL_LANG']['MSC']['fileManager'],
                    'style="vertical-align:text-bottom;"'
                )
                . '</a>';
            $arrField['strField'] = $this->strField . '__' . $strKey;

            // Add title at modal window.
            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$arrField['strField']]['label'][0] =
                (is_array($arrField['label']) && $arrField['label'][0] != '') ? $arrField['label'][0] : $strKey;
        } elseif ($arrField['inputType'] == 'tableWizard') {
            // Add the table import wizard
            $xlabel .= ' <a href="'
                . $this->addToUrl('key=table')
                . '" title="'
                . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][1])
                . '" onclick="Backend.getScrollOffset();">'
                . Image::getHtml(
                    'tablewizard.gif',
                    $GLOBALS['TL_LANG']['MSC']['tw_import'][0],
                    'style="vertical-align:text-bottom;"'
                )
                . '</a>';

            $xlabel .= ' '
                . Image::getHtml(
                    'demagnify.gif',
                    '',
                    'title="'
                    . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_shrink'])
                    . '" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(0.9);"'
                )
                . Image::getHtml(
                    'magnify.gif',
                    '',
                    'title="'
                    . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_expand'])
                    . '" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(1.1);"'
                );
        } elseif ($arrField['inputType'] == 'listWizard') {
            // Add the list import wizard
            $xlabel .= ' <a href="'
                . $this->addToUrl('key=list')
                . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['lw_import'][1])
                . '" onclick="Backend.getScrollOffset();">'
                . Image::getHtml(
                    'tablewizard.gif',
                    $GLOBALS['TL_LANG']['MSC']['tw_import'][0],
                    'style="vertical-align:text-bottom;"'
                )
                . '</a>';
        }

        // Input field callback
        if (isset($arrField['input_field_callback']) && is_array($arrField['input_field_callback'])) {
            if (!is_object($this->{$arrField['input_field_callback'][0]})) {
                $this->import($arrField['input_field_callback'][0]);
            }

            return $this
                ->{$arrField['input_field_callback'][0]}
                ->{$arrField['input_field_callback'][1]}($this, $xlabel);
        }

        $strClass = $GLOBALS[(TL_MODE == 'BE' ? 'BE_FFL' : 'TL_FFL')][$arrField['inputType']];

        if ($strClass == '' || !class_exists($strClass)) {
            return null;
        }

        $arrField['eval']['required'] = false;

        // Use strlen() here (see #3277)
        if (isset($arrField['eval']['mandatory']) && $arrField['eval']['mandatory']) {
            if (is_array($this->varValue[$intRow][$strKey])) {
                if (empty($this->varValue[$intRow][$strKey])) {
                    $arrField['eval']['required'] = true;
                }
            } else {
                if (!strlen($this->varValue[$intRow][$strKey])) {
                    $arrField['eval']['required'] = true;
                }
            }
        }

        // Hide label except if multiple widgets are in one column
        if (!isset($arrField['eval']['columnPos']) || empty($arrField['eval']['columnPos'])) {
            $arrField['eval']['tl_class'] = trim(($arrField['eval']['tl_class'] ?? '') . ' hidelabel');
        }

        // add class to enable easy updating of "name" attributes etc.
        $arrField['eval']['tl_class'] = trim(($arrField['eval']['tl_class'] ?? '') . ' mcwUpdateFields');

        // if we have a row class, add that one aswell.
        if (isset($arrField['eval']['rowClasses'][$intRow])) {
            $arrField['eval']['tl_class'] = trim(
                $arrField['eval']['tl_class'] . ' ' . $arrField['eval']['rowClasses'][$intRow]
            );
        }

        // load callback
        if (isset($arrField['load_callback']) && is_array($arrField['load_callback'])) {
            foreach ($arrField['load_callback'] as $callback) {
                $this->import($callback[0]);
                $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
            }
        } elseif (isset($arrField['load_callback']) && is_callable($arrField['load_callback'])) {
            $varValue = $arrField['load_callback']($varValue, $this);
        }

        // Convert date formats into timestamps (check the eval setting first -> #3063)
        $rgxp               = ($arrField['eval']['rgxp'] ?? '');
        $dateFormatErrorMsg = '';
        if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '') {
            try {
                $objDate = new Date($varValue, $this->getNumericDateFormat($rgxp));
            } catch (\Exception $e) {
                $dateFormatErrorMsg = $e->getMessage();
            }

            $varValue = $objDate->tstamp;
        }

        // Set the translation
        if (!isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['label']) && isset($arrField['label'])) {
            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['label'] = $arrField['label'];
        }

        // Setup the settings.
        $arrField['activeRow']         = $intRow;
        $arrField['name']              = $this->strName . '[' . $intRow . '][' . $strKey . ']';
        $arrField['id']                = $this->strId . '_row' . $intRow . '_' . $strKey;
        $arrField['value']             = ((null !== $varValue) ? $varValue : ($arrField['default'] ?? null));
        $arrField['eval']['tableless'] = true;

        $arrData = $this->handleDcGeneral($arrField, $strKey);

        $objWidget = $this->buildWidget($strClass, $arrData, $arrField);

        $objWidget->strId         = $arrField['id'];
        $objWidget->storeValues   = true;
        $objWidget->xlabel        = $xlabel;
        $objWidget->currentRecord = $this->currentRecord;
        if (!empty($dateFormatErrorMsg)) {
            $objWidget->addError($e->getMessage());
        }


        return $objWidget;
    }

    /**
     * Build the widget.
     *
     * @param string $strClass The widget class name.
     *
     * @param array  $arrData  The data.
     *
     * @param array  $arrField The fields.
     *
     * @return Widget
     */
    private function buildWidget($strClass, array $arrData, array &$arrField)
    {
        // Check if the data container driver is an DC-General.
        if (is_subclass_of($this->objDca, 'ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface')) {
            return $this->buildWidgetForDcGeneral($arrData, $arrField);
        }

        return new $strClass(self::getAttributesFromDca(
            $arrData,
            $arrField['name'],
            $arrField['value'],
            ($arrField['strField'] ?? null),
            $this->strTable,
            $this
        ));
    }

    /**
     * Build the widget with the widget manager from dc general.
     *
     * @param array $arrData  The data.
     *
     * @param array $arrField The field data.
     *
     * @return Widget
     */
    private function buildWidgetForDcGeneral(array $arrData, array &$arrField)
    {
        $environment = $this->objDca->getEnvironment();
        $properties  = $environment->getDataDefinition()->getPropertiesDefinition();

        // Convert the property name for find the property in the definition.
        $search       = array('/([\[][0-9]{1,}[\]])/', '/[\[\]]/');
        $replace      = array('__', '');
        $propertyName = trim(preg_replace($search, $replace, $this->id), '__');

        $propertyClass = new \ReflectionClass($properties->getProperty($propertyName));
        $property      = $propertyClass->newInstance($arrField['name']);
        $properties->addProperty($property);

        $arrField['id'] = $arrField['name'];

        if (is_array($arrData['label'])) {
            $property->setLabel($arrData['label'][0]);
        } else {
            $property->setLabel($arrData['label']);
        }

        $property->setWidgetType($arrField['inputType']);
        if (isset($arrField['eval'])) {
            $property->setExtra($arrField['eval']);
        }
        if (isset($arrField['description'])) {
            $property->setDescription($arrField['description']);
        }
        if (isset($arrField['default'])) {
            $property->setDefaultValue($arrField['default']);
        }
        if (isset($arrData['options'])) {
            $property->setOptions($arrData['options']);
        }
        if (isset($arrField['reference'])) {
            $property->setExtra(
                array_merge(
                    (array) $property->getExtra(),
                    array('reference' => $arrField['reference'])
                )
            );
        }

        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->getEmptyModel();
        $model->setId(9999999);
        $model->setProperty($property->getName(), $arrField['value']);

        if (TL_MODE === 'FE') {
            $manager = new \ContaoCommunityAlliance\DcGeneral\ContaoFrontend\View\WidgetManager($environment, $model);
        } else {
            $manager = new \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager(
                $environment,
                $model
            );
        }

        $widget = $manager->getWidget($property->getName());

        $properties->removeProperty($property);

        return $widget;
    }

    /**
     * Check if DcGeneral version 2+ is calling us and if so, handle GetPropertyOptionsEvent accordingly.
     *
     * @param array  $arrData The field configuration array.
     *
     * @param string $strName The field name in the form.
     *
     * @return array The processed field configuration array.
     */
    public function handleDcGeneral($arrData, $strName)
    {
        // DcGeneral 2.0 compatibility check.
        if (is_subclass_of($this->objDca, 'ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface')) {
            // If options-callback registered, call that one first as otherwise \Widget::getAttributesFromDca will kill
            // our options.
            if (isset($arrData['options_callback']) && is_array($arrData['options_callback'])) {
                $arrCallback        = $arrData['options_callback'];
                $arrData['options'] = System::importStatic($arrCallback[0])->{$arrCallback[1]}($this);
                unset($arrData['options_callback']);
            } elseif (isset($arrData['options_callback']) && is_callable($arrData['options_callback'])) {
                $arrData['options'] = $arrData['options_callback']($this);
                unset($arrData['options_callback']);
            }

            /** @var EnvironmentInterface $environment */
            $environment = $this->objDca->getEnvironment();

            $event = new GetOptionsEvent(
                $this->strName,
                $strName,
                $environment,
                $this->objDca->getModel(),
                $this,
                (array_key_exists('options', $arrData) ? $arrData['options'] : null)
            );
            $environment->getEventDispatcher()->dispatch($event, $event::NAME);

            if ($event->getOptions() !== ($arrData['options'] ?? null)) {
                $arrData['options'] = $event->getOptions();
            }
        }

        return $arrData;
    }

    /**
     * Add specific field data to a certain field in a certain row
     *
     * @param int    $intIndex Row index.
     *
     * @param string $strField Field name.
     *
     * @param array  $arrData  Field data.
     *
     * @return void
     */
    public function addDataToFieldAtIndex($intIndex, $strField, $arrData)
    {
        $this->arrRowSpecificData[$intIndex][$strField] = $arrData;
    }

    /**
     * Generates a table formatted MCW
     *
     * @param array  $arrUnique       The array of unique fields.
     *
     * @param array  $arrDatepicker   Datapicker information.
     *
     * @param array  $arrColorpicker  Colorpicker information.
     *
     * @param string $strHidden       Hidden information.
     *
     * @param array  $arrItems        List of items.
     *
     * @param array  $arrHiddenHeader List of hidden headers.
     *
     * @param bool   $onlyRows        Flag if the only want some rows.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function generateTable(
        $arrUnique,
        $arrDatepicker,
        $arrColorpicker,
        $strHidden,
        $arrItems,
        $arrHiddenHeader = array(),
        $onlyRows = false
    ) {

        $return = '';

        if ($onlyRows == false) {
            // Generate header fields if not all are hidden.
            if (count($this->columnFields) !== count($arrHiddenHeader)) {
                foreach ($this->columnFields as $strKey => $arrField) {
                    if (isset($arrField['eval']['columnPos']) && $arrField['eval']['columnPos']) {
                        $arrHeaderItems[$arrField['eval']['columnPos']] = '<th></th>';
                    } else {
                        if (
                            (isset($arrField['eval']['hideBody'])
                            && true === $arrField['eval']['hideBody'])
                            && (isset($arrField['eval']['hideHead'])
                            && true === $arrField['eval']['hideHead'])
                        ) {
                            $strHeaderItem = (array_key_exists($strKey, $arrHiddenHeader))
                                ? '<th class="hidden">'
                                : '<th>';
                        } else {
                            $strHeaderItem = '<th>'
                                . (array_key_exists($strKey, $arrHiddenHeader) ? '<div class="hidden">' : '');
                        }
                        if (isset($arrField['eval']['mandatory']) && $arrField['eval']['mandatory']) {
                            $strHeaderItem .= '<span class="invisible">'
                            . $GLOBALS['TL_LANG']['MSC']['mandatory']
                            . ' </span>';
                        }
                        $strHeaderItem .=
                        (
                            (is_array($arrField['label'] ?? null))
                                ? $arrField['label'][0]
                                : (
                                    (isset($arrField['label']) && $arrField['label'] != null)
                                        ? $arrField['label']
                                        : $strKey
                                )
                        );
                        if (isset($arrField['eval']['mandatory']) && $arrField['eval']['mandatory']) {
                            $strHeaderItem .= '<span class="mandatory">*</span>';
                        }

                        $isDescriptionsSet = is_array($arrField['label'] ?? null)
                                             && isset($arrField['label'][1])
                                             && $arrField['label'][1] != '';
                        $strHeaderItem     .=
                            (
                                ($isDescriptionsSet)
                                ? '<span title="' . $arrField['label'][1] . '"><sup>(?)</sup></span>'
                                : ''
                            );
                        $strHeaderItem     .= (array_key_exists($strKey, $arrHiddenHeader)) ? '</div>' : '';
                        $arrHeaderItems[]  = $strHeaderItem . '</th>';
                    }
                }
            }

            $return = \sprintf(
                '<table %s' .
                ' data-operations="maxCount[%s] minCount[%s] unique[%s] datepicker[%s]' .
                ' colorpicker[%s]"' .
                ' data-name="%s"' .
                ' id="ctrl_%s"' .
                ' class="tl_modulewizard multicolumnwizard">',
                (($this->style) ? (\sprintf('style="%s"', $this->style)) : ('')),
                ($this->maxCount ? $this->maxCount : '0'),
                ($this->minCount ? $this->minCount : '0'),
                implode(',', $arrUnique),
                implode(',', $arrDatepicker),
                implode(',', $arrColorpicker),
                $this->name,
                $this->strId
            );

            if ($this->columnTemplate == '' && is_array($arrHeaderItems)) {
                $return .= \sprintf('<thead><tr>%s<th></th></tr></thead>', implode("\n      ", $arrHeaderItems));
            }

            $return .= '<tbody>';
        } else {
            $return .= '<table>';
        }

        foreach ($arrItems as $k => $arrValue) {
            $return .= \sprintf('<tr data-rowId="%s">', $k);
            foreach ($arrValue as $itemValue) {
                if ($itemValue['hide'] == true) {
                    $itemValue['tl_class'] .= ' hidden';
                }

                $return .= '<td'
                    . ($itemValue['valign'] != '' ? ' valign="' . $itemValue['valign'] . '"' : '')
                    . ($itemValue['tl_class'] != '' ? ' class="' . $itemValue['tl_class'] . '"' : '')
                    . '>'
                    . $itemValue['entry']
                    . '</td>';
            }

            // insert buttons at the very end
            $return .= '<td class="operations col_last"'
                . (($this->buttonPos != '') ? ' valign="' . $this->buttonPos . '" ' : '')
                . '>'
                . $strHidden;
            $return .= $this->generateButtonString($k);
            $return .= '</td>';
            $return .= '</tr>';
        }

        if ($onlyRows == false) {
            $return .= '</tbody></table>';
            $return .= $this->generateScriptBlock($this->strId, $this->maxCount, $this->minCount);
        } else {
            $return .= '</table>';
        }

        return $return;
    }

    /**
     * Generates the javascript block for the mcw.
     *
     * @param string $strId    The html id of the element.
     *
     * @param int    $maxCount The max amount of rows.
     *
     * @param int    $minCount The min amount of rows.
     *
     * @return string
     */
    protected function generateScriptBlock($strId, $maxCount, $minCount)
    {
        $script = <<<SCRIPT

<script>
window.addEvent("domready", function() {
    window["MCW_" + %s] = new MultiColumnWizard({
        table: "ctrl_" + %s,
        maxCount: %s,
        minCount: %s,
        uniqueFields: []
    });
});
</script>
SCRIPT;

        return sprintf(
            $script,
            json_encode($strId),
            json_encode($strId),
            intval($maxCount),
            intval($minCount)
        );
    }

    /**
     * Generates a formatted MCW based on an template.
     *
     * @param array  $arrUnique      The array of unique fields.
     *
     * @param array  $arrDatepicker  Datapicker information.
     *
     * @param array  $arrColorpicker Colorpicker information.
     *
     * @param string $strHidden      Hidden information.
     *
     * @param array  $arrItems       List of items.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function generateTemplateOutput($arrUnique, $arrDatepicker, $arrColorpicker, $strHidden, $arrItems)
    {
        $objTemplate        = new BackendTemplate($this->columnTemplate);
        $objTemplate->items = $arrItems;

        $arrButtons = array();
        foreach (array_keys($arrItems) as $k) {
            $arrButtons[$k] = $this->generateButtonString($k);
        }
        $objTemplate->buttons = $arrButtons;

        return $objTemplate->parse();
    }

    /**
     * Generates a div formatted MCW
     *
     * @param array  $arrUnique       The array of unique fields.
     *
     * @param array  $arrDatepicker   Datapicker information.
     *
     * @param array  $arrColorpicker  Colorpicker information.
     *
     * @param string $strHidden       Hidden information.
     *
     * @param array  $arrItems        List of items.
     *
     * @param array  $arrHiddenHeader List of hidden headers.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateDiv(
        $arrUnique,
        $arrDatepicker,
        $arrColorpicker,
        $strHidden,
        $arrItems,
        $arrHiddenHeader = array()
    ) {
        // generate header fields
        foreach ($this->columnFields as $strKey => $arrField) {
            if (array_key_exists($strKey, $arrHiddenHeader)) {
                $strKey = $strKey . ' hidden';
            }

            $arrHeaderItems[] = sprintf(
                '<div class="%s">%s</div>',
                $strKey,
                ($arrField['label'][0] ? $arrField['label'][0] : $strKey)
            );
        }

        $return  = '<div'
            . (($this->style) ? (' style="' . $this->style . '"') : '')
            . ' data-operations="maxCount['
            . ($this->maxCount ? $this->maxCount : '0')
            . '] minCount['
            . ($this->minCount ? $this->minCount : '0')
            . '] unique['
            . implode(
                ',',
                $arrUnique
            )
            . '] datepicker['
            . implode(
                ',',
                $arrDatepicker
            )
            . '] colorpicker['
            . implode(
                ',',
                $arrColorpicker
            )
            . ']" id="ctrl_'
            . $this->strId
            . '" class="tl_modulewizard multicolumnwizard">';
        $return .= '<div class="header_fields">' . implode('', $arrHeaderItems) . '</div>';


        // new array for items so we get rid of the ['entry'] and ['valign']
        $arrReturnItems = array();

        foreach ($arrItems as $itemKey => $itemValue) {
            if ($itemValue['hide']) {
                $itemValue['tl_class'] .= ' hidden';
            }

            $arrReturnItems[$itemKey] = '<div'
                . ($itemValue['tl_class'] != '' ? ' class="' . $itemValue['tl_class'] . '"' : '')
                . '>'
                . $itemValue['entry']
                . '</div>';
        }

        $return .= implode('', $arrReturnItems);


        $return .= '<div class="col_last buttons">' . $this->generateButtonString($strKey) . '</div>';

        $return .= $strHidden;

        return $return . '</div>';
    }

    /**
     * Generate the HTML for the operation buttons, as string.
     *
     * @param int $level The level.
     *
     * @return string The HTML with all buttons.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function generateButtonString($level = 0)
    {
        $return = '';
        foreach ($this->arrButtons as $button => $image) {
            // If we have no images go to the next image.
            if ($image === false) {
                continue;
            }

            $btnName = \sprintf('tw_r%s', StringUtil::specialchars($button));
            $return .=
                \sprintf(
                    '<a data-operations="%s" href="%s" class="widgetImage op-%s" title="%s"
                         onclick="return false;">%s</a>',
                    $button,
                    $this->addToUrl(
                        \sprintf(
                            '&%s=%s&cid=%s&id=',
                            $this->strCommand,
                            $button,
                            $level,
                            $this->currentRecord
                        )
                    ),
                    $button,
                    $GLOBALS['TL_LANG']['MSC'][$btnName],
                    Image::getHtml(
                        $image,
                        $GLOBALS['TL_LANG']['MSC'][$btnName],
                        'class="tl_listwizard_img"'
                    )
                );
        }

        return $return;
    }

    /**
     * Get Time/Date-format from global config (BE) or Page settings (FE)
     *
     * @param string $rgxp The rgxp to use.
     *
     * @return mixed
     */
    private function getNumericDateFormat($rgxp)
    {
        return call_user_func(array('\Contao\Date', 'getNumeric' . ucfirst($rgxp) . 'Format'));
    }
}
