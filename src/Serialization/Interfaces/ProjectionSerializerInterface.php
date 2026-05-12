<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;

interface ProjectionSerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(ProjectionInterface $projection): array;
}
