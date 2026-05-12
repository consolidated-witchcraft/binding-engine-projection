<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Extraction;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\Interfaces\ProjectionExtractorInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionSet;

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
