<?php

namespace MenAtWork\MultiColumnWizardBundle\Event;

use Contao\DataContainer;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetTinyMceEvent
 *
 * @package MenAtWork\MultiColumnWizardBundle\Event
 */
class GetTinyMceEvent extends Event
{
    /**
     * Name of the event.
     */
    const NAME = 'men-at-work.multi-column-wizard.get-tiny-mce';

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $build;

    /**
     * @var string
     */
    private $tinyMce;

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
     * The TinyMce string.
     *
     * @param string $string The TinyMce string.
     *
     * @return $this
     */
    public function setTinyMce($string)
    {
        $this->tinyMce = $string;

        return $this;
    }

    /**
     * Get the TinyMce string.
     *
     * @return string
     */
    public function getTinyMce()
    {
        return $this->tinyMce;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
}