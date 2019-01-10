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

use Contao\Config;
use Contao\Date;
use Contao\Image;
use MenAtWork\MultiColumnWizardBundle\Event\GetDatePickerStringEvent;

/**
 * Class CreateDatePicker
 */
class DatePicker
{
    /**
     * Get Time/Date-format from global config (BE) or Page settings (FE)
     *
     * @param string $rgxp The rgxp for the date.
     *
     * @return mixed
     */
    private function getNumericDateFormat($rgxp)
    {
        return call_user_func(array('\Contao\Date', 'getNumeric' . ucfirst($rgxp) . 'Format'));
    }

    /**
     * Listener for building the tiny mce.
     *
     * @param GetDatePickerStringEvent $event The event.
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
     * Generate the date picker Script.
     *
     * @param GetDatePickerStringEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
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
            $strOnSelect = ",\n        onSelect: function() { Backend.autoSubmit(\"" . $table . '"); }';
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
