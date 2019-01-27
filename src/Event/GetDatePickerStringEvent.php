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

namespace MenAtWork\MultiColumnWizardBundle\Event;

/**
 * Class GetTinyMceEvent
 */
class GetDatePickerStringEvent extends GetStringEvent
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard-bundle.get-date-picker';

    /**
     * The date picker string.
     *
     * @var string
     */
    private $datePicker;

    /**
     * The field name.
     *
     * @var string
     */
    private $fieldName;

    /**
     * The Contao rgxp to use for the date.
     *
     * @var string
     */
    private $rgxp;

    /**
     * GetDatePickerEvent constructor.
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
     */
    public function __construct(
        string $fieldId,
        string $tableName,
        array $fieldConfiguration,
        string $fieldName,
        string $rgxp
    ) {
        parent::__construct($fieldId, $tableName, $fieldConfiguration);

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
     * Get the field name.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Get the rgxp to use for the date.
     *
     * @return string
     */
    public function getRgxp(): string
    {
        return $this->rgxp;
    }
}
