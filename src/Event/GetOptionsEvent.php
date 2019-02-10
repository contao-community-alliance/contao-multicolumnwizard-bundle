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

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard as BundleMultiColumnWizard;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired when a MultiColumnWizard wants to retrieve the options for a sub widget in dc-general context.
 */
class GetOptionsEvent extends Event
{
    /**
     * Name of this event.
     */
    const NAME = 'men-at-work.multi-column-wizard-bundle.get-options';

    /**
     * The name of the multi column wizard.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * The name of the sub widget.
     *
     * @var string
     */
    protected $subPropertyName;

    /**
     * The environment in use.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * The current model.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * The multi column wizard.
     *
     * @var \MultiColumnWizard
     */
    protected $widget;

    /**
     * The options array.
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new instance.
     *
     * @param string                                     $propertyName    The name of the multi column wizard widget.
     *
     * @param string                                     $subPropertyName The name of the sub widget.
     *
     * @param EnvironmentInterface                       $environment     The environment instance.
     *
     * @param ModelInterface                             $model           The current model.
     *
     * @param \MultiColumnWizard|BundleMultiColumnWizard $widget          The multi column wizard instance.
     *
     * @param array                                      $options         The current options (defaults to empty array).
     */
    public function __construct(
        $propertyName,
        $subPropertyName,
        EnvironmentInterface $environment,
        ModelInterface $model,
        $widget,
        $options = array()
    ) {
        $this->propertyName    = $propertyName;
        $this->subPropertyName = $subPropertyName;
        $this->environment     = $environment;
        $this->model           = $model;
        $this->widget          = $widget;
        $this->options         = $options;
    }

    /**
     * Retrieve the name of the multi column wizard property.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Retrieve the name of the property within the multi column wizard.
     *
     * @return string
     */
    public function getSubPropertyName()
    {
        return $this->subPropertyName;
    }

    /**
     * Retrieve the dc-general environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Retrieve the model in dc-general scope.
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Retrieve the multi column wizard instance emitting the event.
     *
     * @return \MultiColumnWizard
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Retrieve the options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the options.
     *
     * @param array $options The options.
     *
     * @return GetOptionsEvent
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}
