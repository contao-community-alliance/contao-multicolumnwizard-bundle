<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @copyright  MEN AT WORK 2012, certo web & design GmbH 2012
 * @package    MultiColumnWizard
 * @license    LGPL
 * @filesource
 */

$GLOBALS['BE_FFL']['multiColumnWizard'] = '\MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][]  = array(
    'maw.mcw.events.listener.load_data_container',
    'supportModalSelector'
);
$GLOBALS['TL_HOOKS']['initializeSystem'][]   = array(
    'maw.mcw.events.listener.initialize_system',
    'changeAjaxPostActions'
);
$GLOBALS['TL_HOOKS']['executePostActions'][] = array(
    'maw.mcw.events.listener.execute_post_actions',
    'executePostActions'
);
$GLOBALS['TL_HOOKS']['executePostActions'][] = array(
    'maw.mcw.events.listener.execute_post_actions',
    'handleRowCreation'
);

if (TL_MODE == 'BE') {
    $GLOBALS['TL_HOOKS']['parseTemplate'][] = array(
        'maw.mcw.events.listener.parse_template',
        'addScriptsAndStyles'
    );
}
