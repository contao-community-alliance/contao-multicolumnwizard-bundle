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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Test;

use Contao\System;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard as MultiColumnWizardBundle;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;
use MenAtWork\MultiColumnWizardBundle\Test\Fixture\Issue39Fixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class test the overwrite of the older namespace of previous versions.
 *
 * @coversNothing
 */
class DeprecatedAutoloaderTest extends TestCase
{
    /**
     * Aliases of old classes to the new one.
     *
     * @var array
     */
    private static $classes = [
        'MultiColumnWizard' => MultiColumnWizardBundle::class,
    ];

    /**
     * Provide the alias class map.
     *
     * @return array
     */
    public function provideAliasClassMap()
    {
        $values = [];
        foreach (static::$classes as $alias => $class) {
            $values[] = [$alias, $class];
        }

        return $values;
    }

    /**
     * Test if the deprecated classes are aliased to the new one.
     *
     * @param string $oldClass Old class name.
     *
     * @param string $newClass New class name.
     *
     * @dataProvider provideAliasClassMap
     *
     * @return void
     */
    public function testDeprecatedClassesAreAliased($oldClass, $newClass)
    {
        $this->assertTrue(class_exists($oldClass), sprintf('Class alias "%s" is not found.', $oldClass));
        $oldClassReflection = new \ReflectionClass($oldClass);
        $newClassReflection = new \ReflectionClass($newClass);
        $this->assertSame($newClassReflection->getFileName(), $oldClassReflection->getFileName());
    }

    /**
     * Test for issue #39 bc break for aliased class in root namespace.
     *
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testIssue39()
    {
        System::setContainer($container = $this->getMockForAbstractClass(ContainerInterface::class));
        $container->method('has')->willReturn(true);
        $container
            ->method('get')
            ->willReturnCallback(function ($service) {
                switch ($service) {
                    case 'contao.resource_locator':
                        $locator = $this->getMockBuilder(\stdClass::class)->setMethods(['locate'])->getMock();
                        $locator->method('locate')->willReturn([]);
                        return $locator;
                    case 'event_dispatcher':
                    default:
                        return null;
                }
            });
        define('TL_MODE', 'TEST');
        define('TL_ROOT', sys_get_temp_dir());

        $mcw   = new MultiColumnWizard();
        $dummy = new Issue39Fixture();

        $dummy->testing($mcw);

        // If we end up here, we have succeeded.
        $this->addToAssertionCount(1);
    }
}
