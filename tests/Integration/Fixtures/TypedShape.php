<?php

declare(strict_types=1);

namespace FundiStadi\PostGIS\Tests\Integration\Fixtures;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'typed_shape')]
class TypedShape
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'multipolygon')]
    public ?string $area = null;
}
