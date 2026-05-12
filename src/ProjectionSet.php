<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection;

use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;

readonly class ProjectionSet implements ProjectionSetInterface
{
    /**
     * @param list<ProjectionInterface> $projections
     */
    public function __construct(
        private array $projections,
    ) {
    }

    /**
     * @return list<ProjectionInterface>
     */
    public function getProjections(): array
    {
        return $this->projections;
    }

    public function isEmpty(): bool
    {
        return $this->projections === [];
    }

    public function count(): int
    {
        return count($this->projections);
    }

    public function first(): ?ProjectionInterface
    {
        return $this->projections[0] ?? null;
    }

    /**
     * @return list<ProjectionInterface>
     */
    public function getByProjectionType(string $projectionType): array
    {
        $matches = [];

        foreach ($this->projections as $projection) {
            if ($projection->getProjectionType() === $projectionType) {
                $matches[] = $projection;
            }
        }

        return $matches;
    }
}
