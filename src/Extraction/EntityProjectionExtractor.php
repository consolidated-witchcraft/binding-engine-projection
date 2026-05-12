<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Extraction;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\ProjectionExtractionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\Interfaces\ProjectionExtractorInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionSet;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

final readonly class EntityProjectionExtractor implements ProjectionExtractorInterface
{
    /**
     * @throws ProjectionExtractionException
     */
    public function extract(AssertionSetInterface $assertionSet): ProjectionSetInterface
    {
        $projections = [];

        foreach ($assertionSet->getAssertions() as $assertion) {
            if ($assertion->getPayloadShape() !== BindingPayloadShapeEnum::Shorthand) {
                continue;
            }

            $identifier = $assertion->getShorthandValue();

            if ($identifier === null) {
                continue;
            }

            try {
                $projections[] = new EntityProjection(
                    entityType: $assertion->getBindingType(),
                    identifier: $identifier,
                    label: $assertion->getLabel(),
                    originatingAssertion: $assertion,
                );
            } catch (InvalidProjectionException $exception) {
                throw new ProjectionExtractionException(
                    sprintf(
                        'Failed to extract entity projection: %s',
                        $exception->getMessage(),
                    ),
                    previous: $exception,
                );
            }
        }

        return new ProjectionSet($projections);
    }
}
