<?php
/**
 * Created by PhpStorm.
 * User: Stefan Heimes
 * Date: 09.12.2018
 * Time: 18:58
 */

namespace MenAtWork\MultiColumnWizardBundle\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class GetStringEvent extends Event
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $build;


    /**
     * @var array
     */
    private $fieldConfiguration;

    /**
     * @var string
     */
    private $fieldId;
    /**
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