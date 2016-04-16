<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Dummy\Filter;

use Fludio\DoctrineFilter\FilterBuilder;
use Fludio\DoctrineFilter\FilterInterface;
use Fludio\DoctrineFilter\Type\BetweenFilterType;
use Fludio\DoctrineFilter\Type\LikeFilterType;

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
