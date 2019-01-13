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

use Symfony\Component\EventDispatcher\Event;

/**
 * This is the abstract base for events returning a string.
 */
abstract class GetStringEvent extends Event
{
    /**
     * The version of Contao.
     *
     * @var string
     */
    private $version;

    /**
     * The build number of Contao.
     *
     * @var string
     */
    private $build;

    /**
     * The field configuration.
     *
     * @var array
     */
    private $fieldConfiguration;

    /**
     * The id of the field.
     *
     * @var string
     */
    private $fieldId;

    /**
     * The table name.
     *
     * @var string
     */
    private $tableName;

    /**
     * GetTinyMceEvent constructor.
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
     */
    public function __construct(
        string $version,
        string $build,
        string $fieldId,
        string $tableName,
        array $fieldConfiguration
    ) {
        $this->version            = $version;
        $this->build              = $build;
        $this->fieldId            = $fieldId;
        $this->fieldConfiguration = $fieldConfiguration;
        $this->tableName          = $tableName;
    }

    /**
     * The version of contao.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The build of contao.
     *
     * @return string
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * The field id.
     *
     * @return string
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * The configuration of the field.
     *
     * @return array
     */
    public function getFieldConfiguration()
    {
        return $this->fieldConfiguration;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
}
