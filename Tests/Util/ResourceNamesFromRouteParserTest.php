<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Util;

use BiteCodes\RestApiGeneratorBundle\Util\ResourceNamesFromRouteParser;

class ResourceNamesFromRouteParserTest extends \PHPUnit_Framework_TestCase
{
    const PREFIX = 'bite_codes.rest_api_generator_bundle';

    /**
     * @test
     *
     * @dataProvider getRoutes
     * @param $routeName
     * @param $expectedResult
     */
    public function it_returns_resource_names($routeName, $expectedResult)
    {
        $names = ResourceNamesFromRouteParser::getResourceNames($routeName, $this::PREFIX);

        $this->assertEquals($expectedResult, $names);
    }

    /**
     * @test
     *
     * @dataProvider getRoutes
     * @param $routeName
     * @param $expectedResult
     */
    public function it_returns_sub_resource_names($routeName, $expectedResult)
    {
        $names = ResourceNamesFromRouteParser::getSubResourceNames($routeName, $this::PREFIX);

        array_pop($expectedResult);

        $this->assertEquals($expectedResult, $names);
    }

    public function getRoutes()
    {
        return [
            [$this::PREFIX . '.posts.index', ['posts']],
            [$this::PREFIX . '.posts.comments.index', ['posts', 'comments']],
            [$this::PREFIX . '.category.posts.comments.index', ['category', 'posts', 'comments']],
        ];
    }
}
