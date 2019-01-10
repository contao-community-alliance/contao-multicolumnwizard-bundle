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

$GLOBALS['BE_FFL']['multiColumnWizard'] = '\MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard';

$GLOBALS['TL_HOOKS']['loadDataContainer'][]  = array(
    'menatwork.multicolumnwizard-bundle.events_listener.load_data_container',
    'supportModalSelector'
);
$GLOBALS['TL_HOOKS']['initializeSystem'][]   = array(
    'menatwork.multicolumnwizard-bundle.events_listener.initialize_system',
    'changeAjaxPostActions'
);
$GLOBALS['TL_HOOKS']['executePostActions'][] = array(
    'menatwork.multicolumnwizard-bundle.events_listener.execute_post_actions',
    'executePostActions'
);
$GLOBALS['TL_HOOKS']['executePostActions'][] = array(
    'menatwork.multicolumnwizard-bundle.events_listener.execute_post_actions',
    'handleRowCreation'
);

if (TL_MODE == 'BE') {
    $GLOBALS['TL_HOOKS']['parseTemplate'][] = array(
        'menatwork.multicolumnwizard-bundle.events_listener.parse_template',
        'addScriptsAndStyles'
    );
}
