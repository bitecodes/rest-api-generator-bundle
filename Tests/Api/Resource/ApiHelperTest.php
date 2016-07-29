<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Api\Resource;

use BiteCodes\RestApiGeneratorBundle\Api\Actions\BatchDelete;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\BatchUpdate;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Create;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Delete;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Index;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Show;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Update;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiHelper;

class ApiHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider controllers
     * @test
     *
     * @param string $controller
     * @param string $actionClass
     */
    public function it_returns_the_action_class_for_a_given_controller_name($controller, $actionClass)
    {
        $this->assertEquals($actionClass, ApiHelper::getActionClassFromControllerName($controller));
    }

    /**
     * @return array
     */
    public function controllers()
    {
        return [
            ['bite_codes.rest_api_generator.controller.post:indexAction', Index::class],
            ['bite_codes.rest_api_generator.controller.post:showAction', Show::class],
            ['bite_codes.rest_api_generator.controller.post:createAction', Create::class],
            ['bite_codes.rest_api_generator.controller.post:updateAction', Update::class],
            ['bite_codes.rest_api_generator.controller.post:batch_updateAction', BatchUpdate::class],
            ['bite_codes.rest_api_generator.controller.post:deleteAction', Delete::class],
            ['bite_codes.rest_api_generator.controller.post:batch_deleteAction', BatchDelete::class],
        ];
    }
}
