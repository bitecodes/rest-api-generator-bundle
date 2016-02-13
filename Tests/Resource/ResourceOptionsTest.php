<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Resource;

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
                    'update' => $admin,
                    'delete' => $admin,
                ]
            ]
        ];

        $security = ResourceOptions::getActionSecurity($options);

        $this->assertEquals($security['index'], $user);
        $this->assertEquals($security['show'], $user);
        $this->assertEquals($security['create'], $user);
        $this->assertEquals($security['update'], $admin);
        $this->assertEquals($security['batch_update'], $user);
        $this->assertEquals($security['delete'], $admin);
        $this->assertEquals($security['batch_delete'], $user);
    }

}
