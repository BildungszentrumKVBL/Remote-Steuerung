<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AppBundle.
 *
 * This class represents the AppBundle and has the capability to modify the DIC and configurations.
 * @see [Loading DependencyInjectionContainer extension](http://symfony.com/doc/current/bundles/extension.html)
 */
class AppBundle extends Bundle
{
}
