<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Factory;

use GrinchenkoUniversity\Diia\Dependency\DependencyResolver;
use GrinchenkoUniversity\Diia\Mapper\Request\Acquirers\CreateBranchRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;

class RequestJsonMapperFactory
{
    public function createDefaultMapper(): RequestJsonMapper
    {
        $dependencyResolver = (new DependencyResolver())
            ->addDependency(new ItemsListRequestMapper())
            ->addDependency(new CreateBranchRequestMapper())
        ;

        return new RequestJsonMapper($dependencyResolver);
    }
}
