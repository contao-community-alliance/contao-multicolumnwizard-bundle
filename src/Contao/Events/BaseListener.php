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

use Contao\Controller;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseListener
 *
 * @package MenAtWork\MultiColumnWizardBundle\Contao\Events
 */
class BaseListener extends Controller
{
    /**
     * BaseListener constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Import a library and make it accessible by its name or an optional key
     *
     * @param string $strClass The class name
     *
     * @return mixed|object|string
     */
    protected function getNewClassInstance($strClass)
    {
        $container = System::getContainer();

        if (\is_object($strClass)) {
            return $strClass;
        } elseif ($container->has($strClass) && (strpos($strClass, '\\') !== false || !class_exists($strClass))) {
            return $container->get($strClass);
        } elseif (\in_array('getInstance', get_class_methods($strClass))) {
            return \call_user_func(array($strClass, 'getInstance'));
        } else {
            return new $strClass();
        }
    }

    /**
     * Convert a string to a response object
     * Copy from ajax.
     *
     * @param string $str
     *
     * @return Response
     */
    protected function convertToResponse($str)
    {
        return new Response(\Controller::replaceOldBePaths($str));
    }
}
