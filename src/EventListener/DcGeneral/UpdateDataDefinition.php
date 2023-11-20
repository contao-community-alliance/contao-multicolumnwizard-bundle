<?php

/**
 * This file is part of menatwork/contao-multicolumnwizard-bundle.
 *
 * (c) 2012-2023 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/contao-multicolumnwizard-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2023 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * Class UpdateDataDefinition
 */
class UpdateDataDefinition
{
    /**
     * Add all fields from the MCW to the DCA. This is needed for some fields, because other components need this
     * to create the widget/view etc.
     *
     * @param BuildDataDefinitionEvent $event The event to process.
     *
     * @return void
     */
    public function addMcwFields(BuildDataDefinitionEvent $event)
    {
        // Get the container and all properties.
        $container  = $event->getContainer();
        $properties = $container->getPropertiesDefinition();

        /** @var DefaultProperty $property */
        foreach ($properties as $property) {
            // Only run for mcw.
            if ('multiColumnWizard' !== $property->getWidgetType()) {
                continue;
            }

            // Get the extra and make an own field from it.
            $extra = $property->getExtra();

            // If we have no data here, go to the next.
            if (empty($extra['columnFields']) || !is_array($extra['columnFields'])) {
                continue;
            }

            $this->addPropertyToDefinition($extra, $property, $properties);
        }
    }

    /**
     * Add a property to the definition.
     *
     * @param array                         $extra      The extra data for the property.
     *
     * @param PropertyInterface             $property   The property to add.
     *
     * @param PropertiesDefinitionInterface $properties The definition the property shall be added to.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function addPropertyToDefinition(
        array $extra,
        PropertyInterface $property,
        PropertiesDefinitionInterface $properties
    ) {
        foreach ($extra['columnFields'] as $fieldKey => $fieldConfig) {
            // Build the default name.
            $name = sprintf('%s__%s', $property->getName(), $fieldKey);

            // Make a new field and fill it with the data from the config.
            $subProperty = new DefaultProperty($name);
            foreach ($fieldConfig as $key => $value) {
                switch ($key) {
                    case 'label':
                        if (is_array($value)) {
                            $subProperty->setLabel(reset($value));
                            $subProperty->setDescription(next($value));
                            break;
                        }
                        $subProperty->setLabel($value);
                        break;

                    case 'description':
                        if (!$subProperty->getDescription()) {
                            $subProperty->setDescription($value);
                        }
                        break;

                    case 'default':
                        if (!$subProperty->getDefaultValue()) {
                            $subProperty->setDefaultValue($value);
                        }
                        break;

                    case 'exclude':
                        $subProperty->setExcluded((bool) $value);
                        break;

                    case 'search':
                        $subProperty->setSearchable((bool) $value);
                        break;

                    case 'filter':
                        $subProperty->setFilterable((bool) $value);
                        break;

                    case 'inputType':
                        $subProperty->setWidgetType($value);
                        break;

                    case 'options':
                        $subProperty->setOptions($value);
                        break;

                    case 'explanation':
                        $subProperty->setExplanation($value);
                        break;

                    case 'eval':
                        $subProperty->setExtra(
                            array_merge(
                                (array) $subProperty->getExtra(),
                                (array) $value
                            )
                        );
                        break;

                    case 'reference':
                        $subProperty->setExtra(
                            array_merge(
                                (array) $subProperty->getExtra(),
                                array('reference' => &$value['reference'])
                            )
                        );
                        break;

                    default:
                }
            }

            // Add all to the current list.
            $properties->addProperty($subProperty);
            $this->addSubMultiColumnWizardProperty($subProperty, $properties);
        }
    }

    /**
     * Add a multi column wizard property.
     *
     * @param PropertyInterface             $property   The property.
     * @param PropertiesDefinitionInterface $properties The properties.
     *
     * @return void
     */
    private function addSubMultiColumnWizardProperty(
        PropertyInterface $property,
        PropertiesDefinitionInterface $properties
    ) {
        $extra = $property->getExtra();

        if (empty($extra['columnFields']) || !is_array($extra['columnFields'])) {
            return;
        }

        $this->addPropertyToDefinition($extra, $property, $properties);
    }
}
