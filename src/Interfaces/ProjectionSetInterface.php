<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces;

interface ProjectionSetInterface extends \Countable
{
    /**
     * @return list<ProjectionInterface>
     */
    public function getProjections(): array;

    public function isEmpty(): bool;

    public function first(): ?ProjectionInterface;

    /**
     * @return list<ProjectionInterface>
     */
    public function getByProjectionType(string $projectionType): array;
}
