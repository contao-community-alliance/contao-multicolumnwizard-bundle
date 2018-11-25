<?php

namespace MenAtWork\MultiColumnWizardBundle;

use Contao\CoreBundle\DependencyInjection\Compiler\AddImagineClassPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddResourcesPathsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddSessionBagsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\DoctrineMigrationsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\PickerProviderPass;
use Contao\CoreBundle\DependencyInjection\ContaoCoreExtension;
use MenAtWork\ClipboardBundle\DependencyInjection\ClipboardExtension;
use MenAtWork\MultiColumnWizardBundle\DependencyInjection\MultiColumnWizardExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MultiColumnWizardBundle
 *
 * @package MenAtWork\MultiColumnWizardBundle
 */
class MultiColumnWizardBundle extends Bundle
{
    const SCOPE_BACKEND  = 'backend';
    const SCOPE_FRONTEND = 'frontend';

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MultiColumnWizardExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        // disable automatic command registration
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
