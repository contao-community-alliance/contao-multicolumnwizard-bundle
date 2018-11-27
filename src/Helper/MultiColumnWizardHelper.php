<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  Andreas Schempp 2011
 * @copyright  certo web & design GmbH 2011
 * @copyright  MEN AT WORK 2013
 * @package    MultiColumnWizard
 * @license    LGPL
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Helper;

use Contao\DataContainer;

/**
 * Class MultiColumnWizardHelper
 *
 * @copyright  terminal42 gmbh 2013
 * @author     Ingolf Steinhardt <info@e-spin.de> 2017
 * @package    MultiColumnWizard
 */
class MultiColumnWizardHelper extends \Contao\System
{
    /**
     * Just here to make the constructor public.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generates a filePicker icon for Contao Version > 3.1
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function mcwFilePicker(DataContainer $dc)
    {
        return ' <a href="contao/file.php?do=' . \Input::get('do') . '&amp;table=' . $dc->table . '&amp;field='
               . preg_replace('/_row[0-9]*_/i', '__', $dc->field) . '&amp;value=' . $dc->value . '" title="'
               . specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MSC']['filepicker']))
               . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':765,\'title\':\''
               . specialchars($GLOBALS['TL_LANG']['MOD']['files'][0]) . '\',\'url\':this.href,\'id\':\'' . $dc->field
               . '\',\'tag\':\'ctrl_' . $dc->field
               . ((\Input::get('act') == 'editAll') ? '_' . $dc->id : '')
               . '\',\'self\':this});return false">'
               . \Image::getHtml('pickfile.gif', $GLOBALS['TL_LANG']['MSC']['filepicker'],
                'style="vertical-align:top;cursor:pointer"')
               . '</a>';
    }
}
