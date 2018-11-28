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


use Contao\Session;

class ExecutePostActions
{
    /**
     *
     * @param type $action
     * @param type $dc
     */
    public function executePostActions($action, \DataContainer $dc)
    {
        if ($action == 'reloadFiletree_mcw' || $action == 'reloadPagetree_mcw') {
            //get the fieldname
            $strRef   = Session::getInstance()->get('filePickerRef');
            $strRef   = substr($strRef, stripos($strRef, 'field=') + 6);
            $arrRef   = explode('&', $strRef);
            $strField = $arrRef[0];

            //get value and fieldName
            $strFieldName = \Input::post('name');
            $varValue     = \Input::post('value');

            //get the fieldname parts
            $arrfieldParts = preg_split('/_row[0-9]*_/i', $strFieldName);
            preg_match('/_row[0-9]*_/i', $strFieldName, $arrRow);
            $intRow = substr(substr($arrRow[0], 4), 0, -1);

            //build fieldname
            $strFieldName = $arrfieldParts[0] . '[' . $intRow . '][' . $arrfieldParts[1] . ']';

            $strKey = ($action == 'reloadPagetree_mcw') ? 'pageTree' : 'fileTree';

            // Convert the selected values
            if ($varValue != '') {
                $varValue = trimsplit("\t", $varValue);

                // Automatically add resources to the DBAFS
                if ($strKey == 'fileTree') {
                    if (version_compare(VERSION, '3.1', '>=') && version_compare(VERSION, '3.2', '<')) {
                        $fileId = 'id';
                    }
                    if (version_compare(VERSION, '3.2', '>=')) {
                        $fileId = 'uuid';
                    }
                    foreach ($varValue as $k => $v) {
                        $varValue[$k] = \Dbafs::addResource($v)->$fileId;
                    }
                }

                $varValue = serialize($varValue);
            }

            $arrAttribs['id']       = \Input::post('name');
            $arrAttribs['name']     = $strFieldName;
            $arrAttribs['value']    = $varValue;
            $arrAttribs['strTable'] = $dc->table;
            $arrAttribs['strField'] = $strField;

            $objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);

            // Re-initialize the activeRecord
            $table = \Input::get('table');
            if ($dc->activeRecord == null && \Database::getInstance()->tableExists($table)) {
                $stmt = \Database::getInstance()
                                 ->prepare(sprintf('SELECT * FROM %s WHERE id = ?', $table))
                                 ->execute(\Input::get('id'));

                if ($stmt->numRows > 0) {
                    $dc->activeRecord         = $stmt;
                    $objWidget->dataContainer = $dc;
                }
            }

            echo $objWidget->generate();
            exit;
        }
    }
}