<?php

namespace Fludio\RestApiGeneratorBundle;

use Fludio\RestApiGeneratorBundle\DependencyInjection\EndpointControllerCompilePass;
use Fludio\RestApiGeneratorBundle\DependencyInjection\EndpointManagerCompilePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FludioRestApiGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EndpointControllerCompilePass());
    }

}
