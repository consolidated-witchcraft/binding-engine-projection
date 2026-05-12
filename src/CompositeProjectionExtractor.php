<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionExtractorInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;

final readonly class CompositeProjectionExtractor implements ProjectionExtractorInterface
{
    /**
     * @param list<ProjectionExtractorInterface> $extractors
     */
    public function __construct(
        private array $extractors,
    ) {
    }

    public function extract(AssertionSetInterface $assertionSet): ProjectionSetInterface
    {
        $projections = [];

        foreach ($this->extractors as $extractor) {
            $projectionSet = $extractor->extract($assertionSet);

            foreach ($projectionSet->getProjections() as $projection) {
                $projections[] = $projection;
            }
        }

        return new ProjectionSet($projections);
    }
}
