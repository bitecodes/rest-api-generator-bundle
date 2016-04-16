<?php

namespace BiteCodes\RestApiGeneratorBundle;

use BiteCodes\RestApiGeneratorBundle\DependencyInjection\ApiResourceCompilePass;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\EndpointControllerCompilePass;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\EndpointManagerCompilePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BiteCodesRestApiGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ApiResourceCompilePass());
        $container->addCompilerPass(new EndpointControllerCompilePass());
    }

}
