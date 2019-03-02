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

use Contao\CoreBundle\Framework\Adapter;
use Contao\Image;
use Contao\StringUtil;
use MenAtWork\MultiColumnWizardBundle\Event\GetColorPickerStringEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ColorPicker
 */
class ColorPicker
{
    /**
     * Adapter to the image class.
     *
     * @var Image|Adapter
     */
    private $imageAdapter;

    /**
     * Adapter to the StringUtil class.
     *
     * @var StringUtil|Adapter
     */
    private $stringUtilAdapter;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ColorPicker constructor.
     *
     * @param Adapter|Image       $imageAdapter      Adapter to the image class.
     *
     * @param Adapter|StringUtil  $stringUtilAdapter Adapter to the StringUtil class.
     *
     * @param TranslatorInterface $translator        Translator class.
     */
    public function __construct($imageAdapter, $stringUtilAdapter, $translator)
    {
        $this->imageAdapter      = $imageAdapter;
        $this->stringUtilAdapter = $stringUtilAdapter;
        $this->translator        = $translator;
    }

    /**
     * Generate the TinyMce Script.
     *
     * @param GetColorPickerStringEvent $event The event.
     *
     * @return void
     */
    public function executeEvent(GetColorPickerStringEvent $event)
    {
        // Get some vars.
        $fieldConfiguration = $event->getFieldConfiguration();
        $fieldId            = $event->getFieldId();

        // Support single fields as well (see #5240)
        $fieldId = isset($fieldConfiguration['eval']['multiple']) ? $fieldId . '_0' : $fieldId;

        // Crate the placeholder string.
        $placeHolder = <<<HTML
 %1\$s
<script>
  window.addEvent("domready", function() {
    var cl = $("ctrl_%2\$s").value.hexToRgb(true) || [255, 0, 0];
    new MooRainbow("moo_%2\$s", {
      id: "ctrl_%2\$s",
      startColor: cl,
      imgPath: "assets/colorpicker/images/",
      onComplete: function(color) {
        $("ctrl_%2\$s").value = color.hex.replace("#", "");
      }
    });
  });
</script>
HTML;

        $altText = $this->translator->trans('MSC.colorpicker', [], 'contao_default');
        // Create the image.
        $imageString = $this->imageAdapter->getHtml(
            'pickcolor.svg',
            $altText,
            sprintf(
                'title="%s" id="moo_%s" style="cursor:pointer"',
                $this->stringUtilAdapter->specialchars($altText),
                $fieldId
            )
        );

        // Fill all with live.
        $event->setColorPicker(sprintf($placeHolder, $imageString, $fieldId));
    }
}
