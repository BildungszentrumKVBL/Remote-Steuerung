<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\AsseticBundle\AsseticBundle;
use FOS\UserBundle\FOSUserBundle;
use FR3D\LdapBundle\FR3DLdapBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Gos\Bundle\WebSocketBundle\GosWebSocketBundle;
use Gos\Bundle\PubSubRouterBundle\GosPubSubRouterBundle;
use BCC\CronManagerBundle\BCCCronManagerBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use SunCat\MobileDetectBundle\MobileDetectBundle;
use Fresh\FirebaseCloudMessagingBundle\FreshFirebaseCloudMessagingBundle;
use AppBundle\AppBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Sensio\Bundle\DistributionBundle\SensioDistributionBundle;
use Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new MonologBundle(),
            new SwiftmailerBundle(),
            new DoctrineBundle(),
            new SensioFrameworkExtraBundle(),
            new AsseticBundle(),
            new FOSUserBundle(),
            new FR3DLdapBundle(),
            new DoctrineFixturesBundle(),
            new FOSJsRoutingBundle(),
            new GosWebSocketBundle(),
            new GosPubSubRouterBundle(),
            new BCCCronManagerBundle(),
            new WebProfilerBundle(),
            new MobileDetectBundle(),
            new FreshFirebaseCloudMessagingBundle(),
            new AppBundle(),
            new Symfony\Bundle\WebServerBundle\WebServerBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new DebugBundle();
            $bundles[] = new SensioDistributionBundle();
            $bundles[] = new SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
