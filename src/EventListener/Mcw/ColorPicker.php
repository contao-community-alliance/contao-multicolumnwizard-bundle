<?php

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use Contao\Image;
use MenAtWork\MultiColumnWizardBundle\Event\GetColorPickerStringEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetTinyMceStringEvent;

/**
 * Class ColorPicker
 *
 * @package MenAtWork\MultiColumnWizardBundle\EventListener\Mcw
 */
class ColorPicker
{
    /**
     * Listener for building the tiny mce.
     *
     * @param GetColorPickerStringEvent $event
     *
     * @return void
     */
    public function executeEvent(GetColorPickerStringEvent $event)
    {
        $version = $event->getVersion();
        if (version_compare($version, '4.4', '>=')) {
            $this->contao44x($event);
        }
    }

    /**
     * Generate the TinyMce Script.
     *
     * @param GetColorPickerStringEvent $event
     *
     * @return void
     */
    private function contao44x(GetColorPickerStringEvent $event)
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