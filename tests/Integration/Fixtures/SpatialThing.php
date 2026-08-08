<?php

declare(strict_types=1);

namespace FundiStadi\PostGISBundle\Tests\Integration\Fixtures;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'spatial_thing')]
class SpatialThing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'geometry')]
    public ?string $geom = null;
}
