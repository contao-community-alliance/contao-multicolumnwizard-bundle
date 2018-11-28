<?php

/**
 * This file is part of MultiColumnWizard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MultiColumnWizard
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  Andreas Schempp 2011
 * @copyright  certo web & design GmbH 2011
 * @copyright  MEN AT WORK 2013
 * @license    LGPL
 */

namespace MenAtWork\MultiColumnWizardBundle\Contao\Events;

use Contao\Environment;
use Contao\Input;
use Contao\Session;

/**
 * Class InitializeSystem
 *
 * @package MenAtWork\MultiColumnWizardBundle\Contao\Events
 */
class InitializeSystem
{
    /**
     *
     */
    public function changeAjaxPostActions()
    {
        if (version_compare(VERSION, '3.1', '>=')) {
            if (Environment::get('isAjaxRequest')) {
                switch (Input::post('action')) {
                    case 'reloadPagetree':
                    case 'reloadFiletree':
                        //get the fieldnames
                        $strRef      = Session::getInstance()->get('filePickerRef');
                        $strRef      = substr($strRef, stripos($strRef, 'field=') + 6);
                        $arrRef      = explode('&', $strRef);
                        $arrRefField = explode('__', $arrRef[0]);
                        $arrField    = preg_split('/_row[0-9]*_/i', \Input::post('name'));
                        //change action if modal selector was found
                        if (count($arrRefField) > 1 && $arrRefField === $arrField) {
                            Input::setPost('action', Input::post('action') . '_mcw');
                        }
                        break;
                }
            }
        }

    }
}