<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Controller\RestApiController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityRolesSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'denyAccessUnlessUserHasRole'
        ];
    }

    public function denyAccessUnlessUserHasRole(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];

        if (!$controller instanceof RestApiController) {
            return;
        }

        $roles = $event->getRequest()->attributes->get('_roles');

        if (empty($roles)) {
            return;
        }


        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException();
        }

        if (!$user = $token->getUser()) {
            throw new AccessDeniedException();
        }

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException();
        }

        foreach ($user->getRoles() as $role) {
            if (in_array($role, $roles)) {
                return;
            }
        }

        throw new AccessDeniedException();
    }
}
