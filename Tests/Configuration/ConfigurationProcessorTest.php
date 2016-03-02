<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Configuration;

use Fludio\RestApiGeneratorBundle\DependencyInjection\ConfigurationProcessor;

class ConfigurationProcessorTest extends \PHPUnit_Framework_TestCase
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

        $this->assertEquals($user, ConfigurationProcessor::getActionSecurity($options, 'index'));
        $this->assertEquals($user, ConfigurationProcessor::getActionSecurity($options, 'show'));
        $this->assertEquals($user, ConfigurationProcessor::getActionSecurity($options, 'create'));
        $this->assertEquals($admin, ConfigurationProcessor::getActionSecurity($options, 'update'));
        $this->assertEquals($user, ConfigurationProcessor::getActionSecurity($options, 'batch_update'));
        $this->assertEquals($admin, ConfigurationProcessor::getActionSecurity($options, 'delete'));
        $this->assertEquals($user, ConfigurationProcessor::getActionSecurity($options, 'batch_delete'));
    }
}
