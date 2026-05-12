<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;

interface ProjectionSetSerializerInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function serialize(ProjectionSetInterface $projectionSet): array;
}
