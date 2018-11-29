<?php

namespace MenAtWork\MultiColumnWizardBundle;

use MenAtWork\MultiColumnWizardBundle\DependencyInjection\MultiColumnWizardExtension;
use Symfony\Component\Console\Application;
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
