<?php
/**
 * Created by PhpStorm.
 * User: Stefan Heimes
 * Date: 25.11.2018
 * Time: 15:40
 */

namespace MenAtWork\MultiColumnWizardBundle\Contao\Events;


use Contao\Environment;
use Contao\Input;
use Contao\Session;

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