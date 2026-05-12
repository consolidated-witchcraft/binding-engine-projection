<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Serialization;

use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces\ProjectionSerializerInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces\ProjectionSetSerializerInterface;

final readonly class ProjectionSetArraySerializer implements ProjectionSetSerializerInterface
{
    public function __construct(
        private ProjectionSerializerInterface $projectionSerializer = new ProjectionArraySerializer(),
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serialize(ProjectionSetInterface $projectionSet): array
    {
        $serialized = [];

        foreach ($projectionSet->getProjections() as $projection) {
            $serialized[] = $this->projectionSerializer->serialize($projection);
        }

        return $serialized;
    }
}
