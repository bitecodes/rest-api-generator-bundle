<?php

namespace BiteCodes\RestApiGeneratorBundle\Filter;

use BiteCodes\DoctrineFilter\FilterBuilder;
use BiteCodes\DoctrineFilter\FilterInterface;

class EmptyFilter implements FilterInterface
{
    public function buildFilter(FilterBuilder $builder)
    {
    }
}