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
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2020 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Contao\Widgets;

use Contao\BackendTemplate;
use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
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
     * Field list of unique fields.
     *
     * @var array
     */
    protected $arrUnique = [];

    /**
     * Field list of datepicker fields.
     *
     * @var array
     */
    protected $arrDatepicker = [];

    /**
     * Field list of color picker fields.
     *
     * @var array
     */
    protected $arrColorpicker = [];

    /**
     * Field list of tineMce fields.
     *
     * @var array
     */
    protected $arrTinyMCE = [];

    /**
     * @var array|string
     */
    private $strCommand;

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

        $this->eventDispatcher = \System::getContainer()->get('event_dispatcher');
    }

    /**
     * Get Twig. Since i don't know how the best practices is, we will use this to wrap the call.
     *
     * @return \Twig\Environment
     */
    protected function getTwig()
    {
        return \System::getContainer()->get('twig');
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
                $this->varValue = deserialize($varValue, true);

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
        return parent::__get($strKey);
    }

    /**
     * Get the current id of the mcw.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the name of the mcw.
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function getName(): string
    {
        return json_encode($this->strId, JSON_THROW_ON_ERROR);
    }

    /**
     * reformat array if we have only one field
     * from array[]['fieldname'] = value to array[] = value
     * so we have the same behavoir like multiple-checkbox fields
     *
     * @return array|bool|int|object|string|null
     */
    public function getValue()
    {
        if ($this->flatArray) {
            $arrNew = array();

            foreach ($this->varValue as $val) {
                $arrNew[] = $val[key($this->columnFields)];
            }

            return $arrNew;
        }

        return parent::__get('value');
    }

    /**
     * Get the css style string or an empty string.
     *
     * @return string
     */
    public function getStyle(): string
    {
        return (($this->style) ?: '');
    }

    /**
     * Get the mac count of rows.
     *
     * @return int
     */
    public function getMax(): int
    {
        return (int)$this->maxCount;
    }

    /**
     * Get min count of rows.
     *
     * @return int
     */
    public function getMin(): int
    {
        return (int)$this->minCount;
    }

    /**
     * Get list of unique fields.
     *
     * @return array
     */
    public function getUniqueList(): array
    {
        return $this->arrUnique;
    }

    /**
     * Get list of datepicker fields.
     *
     * @return array
     */
    public function getDatepickerList(): array
    {
        return $this->arrDatepicker;
    }

    /**
     * Get list of colorpicker fields.
     *
     * @return array
     */
    public function getColorpickerList(): array
    {
        return $this->arrColorpicker;
    }

    /**
     * Get list of TineMce fields.
     *
     * @return array
     */
    public function getTinyMceList(): array
    {
        return $this->arrTinyMCE;
    }

    /**
     * Get the widget data for the template.
     *
     * @param string $field Name of field.
     *
     * @param int    $row   The number of the row.
     *
     * @return array|null
     */
    protected function getRawWidgetFor($field, $row): ?array
    {
        if (!isset($this->columnFields[$field])) {
            return null;
        }

        $arrField        = $this->columnFields[$field];
        $this->activeRow = $row;
        $blnHiddenBody   = false;

        // load row specific data (useful for example for default values in different rows)
        if (isset($this->arrRowSpecificData[$row][$field])) {
            $arrField = array_merge($arrField, $this->arrRowSpecificData[$row][$field]);
        }

        $objWidget = $this->initializeWidget(
            $arrField,
            $row,
            $field,
            $this->varValue[$row][$field]
        );

        if ($objWidget === null) {
            return null;
        }

        // load errors if there are any
        if (!empty($this->arrWidgetErrors[$field][$row])) {
            foreach ($this->arrWidgetErrors[$field][$row] as $strErrorMsg) {
                $objWidget->addError($strErrorMsg);
            }
        }

        if (is_string($objWidget)) {
            $strWidget = $objWidget;
        } elseif ($arrField['inputType'] === 'hidden') {
            $strWidget = $objWidget->generate();
        } elseif (true === $arrField['eval']['hideBody'] || true === $arrField['eval']['hideHead']) {
            if (true === $arrField['eval']['hideBody']) {
                $blnHiddenBody = true;
            }

            $strWidget = $objWidget->parse();
        } else {
            $additionalCode = [];

            // Date picker.
            $additionalCode['datePicker'] = $this->getMcWDatePickerString(
                $objWidget->id,
                $field,
                null,
                $arrField,
                $this->strTable
            );

            // Color picker.
            $additionalCode['colorPicker'] = $this->getMcWColorPicker(
                $objWidget->id,
                $field,
                $arrField,
                $this->strTable
            );

            // Tiny MCE.
            if ($arrField['eval']['rte'] && strncmp($arrField['eval']['rte'], 'tiny', 4) === 0) {
                $additionalCode['tinyMce']    = $this->getMcWTinyMCEString(
                    $objWidget->id,
                    $arrField,
                    $this->strTable
                );
                $arrField['eval']['tl_class'] .= ' tinymce';
            }

            // Add custom wizard
            $additionalCode['dcaPicker'] = $this->getMcWDcaPickerWizard(
                $objWidget->id,
                $field,
                $arrField,
                $this->strTable
            );

            if ($arrField['wizard']) {
                $wizard = '';

                $dc               = $this->getDcDriver();
                $dc->field        = $field;
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

            // Remove empty elements.
            $additionalCode = array_filter(
                $additionalCode,
                static function ($value) {
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
        if ((version_compare($contaoVersion, '4.4.41', '>=') &&
                version_compare($contaoVersion, '4.5.0', '<')) ||
            version_compare($contaoVersion, '4.7.7', '>=')) {
            $strWidget = str_replace(['reloadFiletree', 'reloadFiletreeDMA'], 'reloadFiletree_mcw', $strWidget);
            $strWidget = str_replace(['reloadPagetree', 'reloadPagetreeDMA'], 'reloadPagetree_mcw', $strWidget);
        }

        return [
            'entry'    => $strWidget,
            'valign'   => $arrField['eval']['valign'],
            'tl_class' => $arrField['eval']['tl_class'],
            'hide'     => $blnHiddenBody,
            'config'   => $arrField
        ];
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
        $this->eventDispatcher->dispatch($event::NAME, $event);

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
        if (!$fieldConfiguration['eval']['datepicker'] || !isset($fieldConfiguration['eval']['rgxp'])) {
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
        $this->eventDispatcher->dispatch($event::NAME, $event);

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
        $this->eventDispatcher->dispatch($event::NAME, $event);

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
        if (!isset($fieldConfiguration['eval']['dcaPicker'])
            || (!\is_array($fieldConfiguration['eval']['dcaPicker'])
                && !$fieldConfiguration['eval']['dcaPicker'] === true)) {
            return '';
        }

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $event = new GetDcaPickerWizardStringEvent(
            $fieldId,
            $tableName,
            $fieldConfiguration,
            $fieldName
        );
        $this->eventDispatcher->dispatch($event::NAME, $event);

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
        $dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'];

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
        $varInput = (array)$varInput;

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
                $rgxp = $arrField['eval']['rgxp'];
                if (!$objWidget->hasErrors()
                    && ($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim')
                    && $varValue != ''
                ) {
                    $objDate  = new Date($varValue, $this->getNumericDateFormat($rgxp));
                    $varValue = $objDate->tstamp;
                }

                // Save callback
                if (is_array($arrField['save_callback'])) {
                    foreach ($arrField['save_callback'] as $callback) {
                        $this->import($callback[0]);

                        try {
                            $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
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
    public function generate($overwriteRowCurrentRow = null, $onlyRows = false): string
    {
        // Load the callback data if there's any (do not do this in __set()
        // already because then we don't have access to currentRecord).
        if (is_array($this->arrCallback)) {
            $this->import($this->arrCallback[0]);
            $this->columnFields = $this->{$this->arrCallback[0]}->{$this->arrCallback[1]}($this);
        }

        // Setup from some information.
        $this->strCommand = 'cmd_' . $this->strField;
        $intNumberOfRows  = max(count($this->varValue), 1);
        // Always show the minimum number of rows if set.
        if ($this->minCount && ($intNumberOfRows < $this->minCount)) {
            $intNumberOfRows = $this->minCount;
        }
        // Declare the current row.
        if ($overwriteRowCurrentRow !== null) {
            $i               = (int) $overwriteRowCurrentRow;
            $intNumberOfRows = ($i + 1);
        } else {
            $i = 0;
        }

        // Run all fields and generate a basic list of information.
        foreach ($this->columnFields as $strKey => $arrField) {
            // Store unique fields
            if ($arrField['eval']['unique']) {
                $this->arrUnique[] = $strKey;
            }

            // Store date picker fields
            if ($arrField['eval']['datepicker']) {
                $this->arrDatepicker[] = $strKey;
            }

            // Store color picker fields
            if ($arrField['eval']['colorpicker']) {
                $this->arrColorpicker[] = $strKey;
            }

            // Store tiny mce fields
            if ($arrField['eval']['rte'] && strncmp($arrField['eval']['rte'], 'tiny', 4) === 0) {
                foreach ($this->varValue as $row => $value) {
                    $tinyId = 'ctrl_' . $this->strField . '_row' . $row . '_' . $strKey;

                    $GLOBALS['TL_RTE']['tinyMCE'][$tinyId] = array(
                        'id'   => $tinyId,
                        'file' => 'tinyMCE',
                        'type' => null
                    );
                }

                $this->arrTinyMCE[] = $strKey;
            }
        }

        return $this->generateOutput($i);
    }


    /**
     * Generates a table formatted MCW
     *
     * @param int $currentRow The current row, needed for particle generation.
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function generateOutput($currentRow = 0): string
    {
        $twigData = [
            'mcw'        => $this,
            'currentRow' => $currentRow
        ];

        $twig = $this->getTwig();

        return $twig->render(
            '@MawMCW/mcw_div.twig',
            $twigData
        );
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
        if ($arrField['eval']['helpwizard']) {
            $xlabel .= ' <a href="contao/help.php?table=' . $this->strTable . '&amp;field=' . $this->strField
                . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['helpWizard'])
                . '" onclick="Backend.openModalIframe({\'width\':735,\'height\':405,\'title\':\''
                . StringUtil::specialchars(str_replace(
                    "'",
                    "\\'",
                    $arrField['label'][0]
                ))
                . '\',\'url\':this.href});return false">'
                . \Image::getHtml(
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

            $xlabel               .= ' <a href="'
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
        if (is_array($arrField['input_field_callback'])) {
            if (!is_object($this->{$arrField['input_field_callback'][0]})) {
                $this->import($arrField['input_field_callback'][0]);
            }

            return $this->{$arrField['input_field_callback'][0]}->{$arrField['input_field_callback'][1]}($this,
                $xlabel);
        }

        $strClass = $GLOBALS[(TL_MODE == 'BE' ? 'BE_FFL' : 'TL_FFL')][$arrField['inputType']];

        if ($strClass == '' || !class_exists($strClass)) {
            return null;
        }

        $arrField['eval']['required'] = false;

        // Use strlen() here (see #3277)
        if ($arrField['eval']['mandatory']) {
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
        if ($arrField['eval']['columnPos'] == '') {
            $arrField['eval']['tl_class'] = trim($arrField['eval']['tl_class'] . ' hidelabel');
        }

        // add class to enable easy updating of "name" attributes etc.
        $arrField['eval']['tl_class'] = trim($arrField['eval']['tl_class'] . ' mcwUpdateFields');

        // if we have a row class, add that one aswell.
        if (isset($arrField['eval']['rowClasses'][$intRow])) {
            $arrField['eval']['tl_class'] = trim(
                $arrField['eval']['tl_class'] . ' ' . $arrField['eval']['rowClasses'][$intRow]
            );
        }

        // load callback
        if (is_array($arrField['load_callback'])) {
            foreach ($arrField['load_callback'] as $callback) {
                $this->import($callback[0]);
                $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
            }
        } elseif (is_callable($arrField['load_callback'])) {
            $varValue = $arrField['load_callback']($varValue, $this);
        }

        // Convert date formats into timestamps (check the eval setting first -> #3063)
        $rgxp               = $arrField['eval']['rgxp'];
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
        if (!isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['label'])) {
            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['label'] = $arrField['label'];
        }

        // Setup the settings.
        $arrField['activeRow']         = $intRow;
        $arrField['name']              = $this->strName . '[' . $intRow . '][' . $strKey . ']';
        $arrField['id']                = $this->strId . '_row' . $intRow . '_' . $strKey;
        $arrField['value']             = (null !== $varValue) ? $varValue : $arrField['default'];
        $arrField['eval']['tableless'] = true;

        $arrData   = $this->handleDcGeneral($arrField, $strKey);
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
            $arrField['strField'],
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

        $property->setLabel($arrData['label']);
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
                    (array)$property->getExtra(),
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
            if (is_array($arrData['options_callback'])) {
                $arrCallback        = $arrData['options_callback'];
                $arrData['options'] = static::importStatic($arrCallback[0])->{$arrCallback[1]}($this);
                unset($arrData['options_callback']);
            } elseif (is_callable($arrData['options_callback'])) {
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
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);

            if ($event->getOptions() !== $arrData['options']) {
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
     * @param array $items List of items.
     *
     * @return array
     */
    protected function getTemplateInformationItems($items)
    {
        $itemsFullData = [];
        foreach ($items as $k => $arrValue) {
            $row = [
                'rowId'      => $k,
                'fields'     => [],
                'operations' => [],
            ];

            // Fields.
            foreach ($arrValue as $fieldKey => $itemValue) {
                $field = [
                    'isHidden' => $itemValue['hide'],
                    'class'    => $itemValue['tl_class'],
                    'value'    => $itemValue['entry']
                ];

                $row['fields'][$fieldKey] = $field;
            }

            // Operations.
            foreach ($this->arrButtons as $button => $image) {
                // If we have no images go to the next image.
                if ($image === false) {
                    continue;
                }

                $btnName                    = \sprintf('tw_r%s', StringUtil::specialchars($button));
                $row['operations'][$button] = [
                    'name'  => $button,
                    'url'   => $this->addToUrl(
                        \sprintf(
                            '&%s=%s&cid=%s&id=',
                            $this->strCommand,
                            $button,
                            $k,
                            $this->currentRecord
                        )
                    ),
                    'label' => $GLOBALS['TL_LANG']['MSC'][$btnName],
                    'image' => [
                        'path'  => Image::getPath($image),
                        'label' => $GLOBALS['TL_LANG']['MSC'][$btnName]
                    ]
                ];
            }

            $itemsFullData[$k] = $row;
        }

        return $itemsFullData;
    }

    /**
     * @param array $arrUnique      The array of unique fields.
     *
     * @param array $arrDatepicker  Datapicker information.
     *
     * @param array $arrColorpicker Colorpicker information.
     *
     * @return array
     */
    protected function getTemplateInformationMeta($arrUnique, $arrDatepicker, $arrColorpicker)
    {
        return [
            'name'        => json_encode($this->strId),
            'id'          => $this->strId,
            'min'         => intval($this->minCount),
            'max'         => intval($this->maxCount),
            'style'       => (($this->style) ?: ''),
            'unique'      => implode(',', $arrUnique),
            'datepicker'  => implode(',', $arrDatepicker),
            'colorpicker' => implode(',', $arrColorpicker),
        ];
    }

    /**
     * Create a list with the information of the header for the twig template.
     *
     * @param array $hiddenHeader A list of the hidden fields.
     *
     * @return array A list with the information.
     */
    protected function getTemplateInformationHeader($hiddenHeader)
    {
        $header = [];
        foreach ($this->columnFields as $strKey => $arrField) {
            $columnHeader = [
                'column'         => $strKey,
                'isHidden'       => false,
                'label'          => '',
                'hasDescription' => false,
                'description'    => '',
            ];

            // Meta information.
            $columnHeader['isHidden'] = key_exists($strKey, $hiddenHeader);

            // Label.
            if (is_array($arrField['label']) && isset($arrField['label'][0])) {
                $columnHeader['label'] = $arrField['label'][0];
            } elseif (is_string($arrField['label'])) {
                $columnHeader['label'] = $arrField['label'];
            }

            // Description.
            if (is_array($arrField['label']) && isset($arrField['label'][1])) {
                $columnHeader['description']    = $arrField['label'][1];
                $columnHeader['hasDescription'] = true;
            }

            $header[$strKey] = $columnHeader;
        }

        return $header;
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
            $return  .=
                \sprintf(
                    '<a data-operations="%s" href="%s" class="widgetImage" title="%s" onclick="return false;">%s</a> ',
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
