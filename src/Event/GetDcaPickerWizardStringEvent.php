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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Event;

/**
 * Class GetColorPickerStringEvent
 */
class GetDcaPickerWizardStringEvent extends GetStringEvent
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard-bundle.get-dca-picker-wizard';

    /**
     * The color picker.
     *
     * @var string
     */
    private $wizard;

    /**
     * The field name.
     *
     * @var string
     */
    private $fieldName;

    /**
     * GetDatePickerEvent constructor.
     *
     * @param string $fieldId            The field id.
     *
     * @param string $tableName          The name of the table.
     *
     * @param array  $fieldConfiguration The configuration of the field.
     *
     * @param string $fieldName          The name of the field.
     */
    public function __construct(
        string $fieldId,
        string $tableName,
        array $fieldConfiguration,
        string $fieldName
    ) {
        parent::__construct($fieldId, $tableName, $fieldConfiguration);

        $this->fieldName = $fieldName;
    }

    /**
     * The Wizard string.
     *
     * @param string $string The ColorPicker string.
     *
     * @return $this
     */
    public function setWizard($string)
    {
        $this->wizard = $string;

        return $this;
    }

    /**
     * Get the Wizard string.
     *
     * @return string
     */
    public function getWizard()
    {
        return $this->wizard;
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
}
