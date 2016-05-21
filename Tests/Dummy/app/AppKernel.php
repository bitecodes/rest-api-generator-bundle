<?php


namespace BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app;

use BiteCodes\FactrineBundle\BiteCodesFactrineBundle;
use BiteCodes\RestApiGeneratorBundle\BiteCodesRestApiGeneratorBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    protected $configFile;

    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new DoctrineBundle(),
            new JMSSerializerBundle(),
            new SensioFrameworkExtraBundle(),
            new BiteCodesFactrineBundle(),
            new BiteCodesRestApiGeneratorBundle()
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }

    /**
     * @param mixed $configFile
     */
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }
}
