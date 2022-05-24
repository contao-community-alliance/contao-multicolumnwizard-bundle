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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Test\EventListener\Mcw;

use Contao\CoreBundle\Framework\Adapter;
use MenAtWork\MultiColumnWizardBundle\Event\GetColorPickerStringEvent;
use MenAtWork\MultiColumnWizardBundle\EventListener\Mcw\ColorPicker;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This tests the color picker event listener.
 *
 * @covers \MenAtWork\MultiColumnWizardBundle\EventListener\Mcw\ColorPicker
 */
class ColorPickerTest extends TestCase
{
    public function testExecuteEvent()
    {
        $stringUtilAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->setMethods(['specialchars'])
            ->disableOriginalConstructor()
            ->getMock();
        $stringUtilAdapter
            ->expects($this->once())
            ->method('specialchars')
            ->with('translated')
            ->willReturn('stripped');

        $imageAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->setMethods(['getHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageAdapter
            ->expects($this->once())
            ->method('getHtml')
            ->with(
                'pickcolor.svg',
                'translated',
                sprintf(
                    'title="%s" id="moo_%s" style="cursor:pointer"',
                    'stripped',
                    'fieldId'
                )
            )
            ->willReturn('<image>');

        $translator = $this->getMockForAbstractClass(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('MSC.colorpicker', [], 'contao_default')
            ->willReturn('translated');

        $listener = new ColorPicker($imageAdapter, $stringUtilAdapter, $translator);

        $event = new GetColorPickerStringEvent('fieldId', 'tableName', [], 'fieldName');

        $listener->executeEvent($event);

        $this->assertSame(
            <<<HTML
 <image>
<script>
  window.addEvent("domready", function() {
    var cl = $("ctrl_fieldId").value.hexToRgb(true) || [255, 0, 0];
    new MooRainbow("moo_fieldId", {
      id: "ctrl_fieldId",
      startColor: cl,
      imgPath: "assets/colorpicker/images/",
      onComplete: function(color) {
        $("ctrl_fieldId").value = color.hex.replace("#", "");
      }
    });
  });
</script>
HTML
            ,
            $event->getColorPicker()
        );
    }
}
