<?php

namespace MenAtWork\MultiColumnWizardBundle\Contao\Events;

use Contao\Environment;
use Contao\Input;

class LoadDataContainer
{
    public function supportModalSelector($strTable)
    {
        if (strpos(\Environment::get('script'), 'contao/file.php') !== false
            || strpos(\Environment::get('script'), 'contao/page.php') !== false) {
            list($strField, $strColumn) = explode('__', \Input::get('field'));
            if ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'] == 'multiColumnWizard') {
                $GLOBALS['TL_DCA'][$strTable]['fields'][$strField . '__' . $strColumn] =
                    $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['eval']['columnFields'][$strColumn];
            }
        }
    }
}