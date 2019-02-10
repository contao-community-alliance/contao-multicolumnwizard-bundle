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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use Contao\Image;
use MenAtWork\MultiColumnWizardBundle\Event\GetColorPickerStringEvent;

/**
 * Class ColorPicker
 */
class ColorPicker
{
    /**
     * Generate the TinyMce Script.
     *
     * @param GetColorPickerStringEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function executeEvent(GetColorPickerStringEvent $event)
    {
        // Get some vars.
        $fieldConfiguration = $event->getFieldConfiguration();
        $fieldId            = $event->getFieldId();

        // Support single fields as well (see #5240)
        $fieldId = $fieldConfiguration['eval']['multiple'] ? $fieldId . '_0' : $fieldId;

        // Crate the placeholder string.
        $placeHolder = <<<HTML
 %s
<script>
  window.addEvent("domready", function() {
    var cl = $("ctrl_%s").value.hexToRgb(true) || [255, 0, 0];
    new MooRainbow("moo_%s", {
      id: "ctrl_%s",
      startColor: cl,
      imgPath: "assets/colorpicker/images/",
      onComplete: function(color) {
        $("ctrl_%s").value = color.hex.replace("#", "");
      }
    });
  });
</script>
HTML;

        // Create the image.
        $imageString = Image::getHtml(
            'pickcolor.svg',
            $GLOBALS['TL_LANG']['MSC']['colorpicker'],
            sprintf(
                'title="%s" id="moo_%s" style="cursor:pointer"',
                \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['colorpicker']),
                $fieldId
            )
        );

        // Fill all with live.
        $string = sprintf(
            $placeHolder,
            $imageString,
            $fieldId,
            $fieldId,
            $fieldId,
            $fieldId
        );

        $event->setColorPicker($string);
    }
}
