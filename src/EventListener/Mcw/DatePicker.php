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

namespace MenAtWork\MultiColumnWizardBundle\EventListener\Mcw;

use Contao\Config;
use Contao\Date;
use Contao\Image;
use MenAtWork\MultiColumnWizardBundle\Event\GetDatePickerStringEvent;

/**
 * Class CreateDatePicker
 *
 * @package MenAtWork\MultiColumnWizardBundle\EventListener\Mcw
 */
class DatePicker
{
    /**
     * Get Time/Date-format from global config (BE) or Page settings (FE)
     *
     * @param $rgxp
     *
     * @return mixed
     */
    private function getNumericDateFormat($rgxp)
    {
        return call_user_func(array("\Contao\Date", "getNumeric" . ucfirst($rgxp) . "Format"));
    }

    /**
     * Listener for building the tiny mce.
     *
     * @param GetDatePickerStringEvent $event
     *
     * @return void
     */
    public function executeEvent(GetDatePickerStringEvent $event)
    {
        $version = $event->getVersion();
        if (version_compare($version, '4.4', '>=')) {
            $this->contao44x($event);
        }
    }

    /**
     * Generate the TinyMce Script.
     *
     * @param GetDatePickerStringEvent $event
     *
     * @return void
     */
    private function contao44x(GetDatePickerStringEvent $event)
    {
        // Get some vars.
        $fieldConfiguration = $event->getFieldConfiguration();
        $table              = $event->getTableName();
        $fieldId            = $event->getFieldId();
        $rgxp               = $event->getRgxp();
        $format             = Date::formatToJs(Config::get($rgxp . 'Format'));

        switch ($rgxp) {
            case 'datim':
                $time = ",\n        timePicker: true";
                break;

            case 'time':
                $time = ",\n        pickOnly: \"time\"";
                break;

            default:
                $time = '';
                break;
        }

        $strOnSelect = '';

        // Trigger the auto-submit function (see #8603)
        if ($fieldConfiguration['eval']['submitOnChange']) {
            $strOnSelect = ",\n        onSelect: function() { Backend.autoSubmit(\"" . $table . "\"); }";
        }

        // Crate the placeholder string.
        $placeHolder = <<<HTML
 %s
<script>
  window.addEvent("domready", function() {
    new Picker.Date($("ctrl_%s"), {
      draggable: false,
      toggle: $("toggle_%s"),
      format: "%s",
      positionOffset: {x:-211,y:-209}%s,
      pickerClass: "datepicker_bootstrap",
      useFadeInOut: !Browser.ie%s,
      startDay: %s,
      titleFormat: "%s"
    });
  });
</script>
HTML;

        // Create the image.
        $imageString = Image::getHtml(
            'assets/datepicker/images/icon.svg',
            '',
            sprintf(
                'title="%s" id="toggle_%s" style="cursor:pointer"',
                \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['datepicker']),
                $fieldId

            )
        );

        // Fill all with live.
        $string = sprintf(
            $placeHolder,
            $imageString,
            $fieldId,
            $fieldId,
            $format,
            $time,
            $strOnSelect,
            $GLOBALS['TL_LANG']['MSC']['weekOffset'],
            $GLOBALS['TL_LANG']['MSC']['titleFormat']
        );

        $event->setDatePicker($string);
    }
}