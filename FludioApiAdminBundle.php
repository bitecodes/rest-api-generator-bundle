<?php

namespace Fludio\ApiAdminBundle;

use Fludio\ApiAdminBundle\DependencyInjection\EndpointControllerCompilePass;
use Fludio\ApiAdminBundle\DependencyInjection\EndpointManagerCompilePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FludioApiAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EndpointControllerCompilePass());
    }

}
