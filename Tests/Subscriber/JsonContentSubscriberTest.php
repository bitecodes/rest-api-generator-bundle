<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Subscriber\JsonContentSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class JsonContentSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function body_can_be_empty()
    {
        $request = new Request([], [], [], [], [], [], '');
        $request->headers->set('Content-Type', 'application/json');

        $subscriber = new JsonContentSubscriber();

        $event = $this->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber->setContentToRequest($event);

        $this->assertEquals([], $request->request->all());
    }
}
