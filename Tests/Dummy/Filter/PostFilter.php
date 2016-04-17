<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Dummy\Filter;

use BiteCodes\DoctrineFilter\FilterBuilder;
use BiteCodes\DoctrineFilter\FilterInterface;
use BiteCodes\DoctrineFilter\Type\BetweenFilterType;
use BiteCodes\DoctrineFilter\Type\LikeFilterType;

class PostFilter implements FilterInterface
{
    public function buildFilter(FilterBuilder $builder)
    {
        $builder
            ->add('title', LikeFilterType::class)
            ->add('content', LikeFilterType::class)
            ->add('createdAt', BetweenFilterType::class);
    }
}
