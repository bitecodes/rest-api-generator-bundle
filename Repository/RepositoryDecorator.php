<?php

namespace BiteCodes\RestApiGeneratorBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Fludio\DoctrineFilter\Traits\EntityFilterTrait;

class RepositoryDecorator
{
    use EntityFilterTrait;

    /**
     * @var EntityRepository
     */
    private $baseRepository;

    public function __construct(EntityRepository $repository)
    {
        $this->baseRepository = $repository;
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->baseRepository, $method)) {
            $message = sprintf("Undefined method %s in class %s.", $method, get_class($this->baseRepository));
            throw new Exception($message);
        }

        return call_user_func_array(array($this->baseRepository, $method), $arguments);
    }
}
