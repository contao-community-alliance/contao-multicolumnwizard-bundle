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
 * @author     Alexander Menk <alex.menk@gmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Dominik Tomasi <d.tomasi@upcom.ch>
 * @author     fritzmg <email@spikx.net>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Martin Auswöger <martin@auswoeger.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Sven Meierhans <s.meierhans@gmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2011 Andreas Schempp
 * @copyright  2011 certo web & design GmbH
 * @copyright  2013-2019 MEN AT WORK
 * @license    https://github.com/menatwork/contao-multicolumnwizard-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MenAtWork\MultiColumnWizardBundle\Helper;

use Contao\DataContainer;
use MenAtWork\MultiColumnWizardBundle\Event\GetDcaPickerWizardStringEvent;

/**
 * Class MultiColumnWizardHelper
 */
class MultiColumnWizardHelper extends \Contao\System
{
    /**
     * Just here to make the constructor public.
     */
    // @codingStandardsIgnoreStart - not useless, we change the visibility.
    public function __construct()
    {
        parent::__construct();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Generates a filePicker icon.
     *
     * @param DataContainer $container The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function mcwFilePicker(DataContainer $container)
    {
        trigger_error(
            sprintf(
                'Use of deprecated function "%s::%s" or "%s::%s". Use instead the event "%s"',
                'MultiColumnWizardHelper',
                __FUNCTION__,
                __CLASS__,
                __FUNCTION__,
                GetDcaPickerWizardStringEvent::NAME
            ),
            E_USER_DEPRECATED
        );

        // Create a new event and dispatch it. Hope that someone have a good solution.
        $eventDispatcher    = \Contao\System::getContainer()->get('event_dispatcher');
        $fieldConfiguration = [
            'label'     => ['MCW - Picker', ''],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'dcaPicker' => [
                    'do'         => 'files',
                    'context'    => 'file',
                    'icon'       => 'pickfile.svg',
                    'fieldType'  => 'checkbox',
                    'filesOnly'  => true
                ]
            ]
        ];
        $event              = new GetDcaPickerWizardStringEvent(
            $container->inputName,
            $container->table,
            $fieldConfiguration,
            $container->inputName
        );
        $eventDispatcher->dispatch($event, $event::NAME);

        return $event->getWizard();
    }
}
