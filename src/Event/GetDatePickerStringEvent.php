<?php

/**
 * This file is part of MultiColumnWizard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MultiColumnWizard
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  Andreas Schempp 2011
 * @copyright  certo web & design GmbH 2011
 * @copyright  MEN AT WORK 2013
 * @license    LGPL
 */

namespace MenAtWork\MultiColumnWizardBundle\Event;

/**
 * Class GetTinyMceEvent
 *
 * @package MenAtWork\MultiColumnWizardBundle\Event
 */
class GetDatePickerStringEvent extends GetStringEvent
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard-bundle.get-date-picker';

    /**
     * @var string
     */
    private $datePicker;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $rgxp;

    /**
     * GetDatePickerEvent constructor.
     *
     * @param string $version            The version of Contao.
     *
     * @param string $build              The build of Contao.
     *
     * @param string $fieldId            The field id.
     *
     * @param string $tableName          The name of the table.
     *
     * @param array  $fieldConfiguration The configuration of the field.
     *
     * @param string $fieldName          TODO: What is this?
     *
     * @param string $rgxp               The rgxp for the date.
     *
     */
    public function __construct(
        string $version,
        string $build,
        string $fieldId,
        string $tableName,
        array $fieldConfiguration,
        string $fieldName,
        string $rgxp
    ) {
        parent::__construct($version, $build, $fieldId, $tableName, $fieldConfiguration);

        $this->fieldName = $fieldName;
        $this->rgxp      = $rgxp;
    }

    /**
     * The DatePicker string.
     *
     * @param string $string The DatePicker string.
     *
     * @return $this
     */
    public function setDatePicker($string)
    {
        $this->datePicker = $string;

        return $this;
    }

    /**
     * Get the DatePicker string.
     *
     * @return string
     */
    public function getDatePicker()
    {
        return $this->datePicker;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getRgxp(): string
    {
        return $this->rgxp;
    }
}