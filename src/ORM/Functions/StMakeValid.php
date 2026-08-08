<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\ORM\Functions;

/**
 * DQL: ST_MakeValid(g) — repairs an invalid geometry without losing vertices.
 */
final class StMakeValid extends AbstractSpatialFunction
{
    protected function functionName(): string
    {
        return 'ST_MakeValid';
    }
}
