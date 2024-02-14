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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     fritzmg <email@spikx.net>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Yanick Witschi <yanick.witschi@certo-net.ch>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;
use MenAtWork\MultiColumnWizardBundle\EventListener\Contao\LoadDataContainer;
use MenAtWork\MultiColumnWizardBundle\EventListener\Contao\InitializeSystem;
use MenAtWork\MultiColumnWizardBundle\EventListener\Contao\ExecutePostActions;

$GLOBALS['BE_FFL']['multiColumnWizard'] = MultiColumnWizard::class;

$GLOBALS['TL_HOOKS']['loadDataContainer'][]  = [LoadDataContainer::class, 'supportModalSelector'];
$GLOBALS['TL_HOOKS']['initializeSystem'][]   = [InitializeSystem::class, 'addSystemNecessaryThings'];
$GLOBALS['TL_HOOKS']['initializeSystem'][]   = [InitializeSystem::class, 'changeAjaxPostActions'];
$GLOBALS['TL_HOOKS']['executePostActions'][] = [ExecutePostActions::class, 'executePostActions'];
$GLOBALS['TL_HOOKS']['executePostActions'][] = [ExecutePostActions::class, 'handleRowCreation'];

/*
 * All Hooks for the BE are moved to the following function:
 * \MenAtWork\MultiColumnWizardBundle\EventListener\Contao\InitializeSystem::addSystemNecessaryThings
 */
