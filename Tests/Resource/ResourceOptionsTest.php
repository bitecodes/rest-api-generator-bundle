<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Resource;

use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
use Fludio\RestApiGeneratorBundle\Resource\ResourceOptions;

class ResourceOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_resolves_security_roles()
    {
        $user = ['ROLE_USER'];
        $admin = ['ROLE_ADMIN'];
        $options = [
            'secure' => [
                'default' => $user,
                'routes' => [
                    ResourceActionData::ACTION_UPDATE => $admin,
                    ResourceActionData::ACTION_DELETE => $admin,
                ]
            ]
        ];

        $security = ResourceOptions::getActionSecurity($options);

        $this->assertEquals($security[ResourceActionData::ACTION_INDEX], $user);
        $this->assertEquals($security[ResourceActionData::ACTION_SHOW], $user);
        $this->assertEquals($security[ResourceActionData::ACTION_CREATE], $user);
        $this->assertEquals($security[ResourceActionData::ACTION_UPDATE], $admin);
        $this->assertEquals($security[ResourceActionData::ACTION_BATCH_UPDATE], $user);
        $this->assertEquals($security[ResourceActionData::ACTION_DELETE], $admin);
        $this->assertEquals($security[ResourceActionData::ACTION_BATCH_DELETE], $user);
    }

}
