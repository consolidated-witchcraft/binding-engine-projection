<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Extraction;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\ProjectionExtractionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\Interfaces\ProjectionExtractorInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionSet;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

final readonly class RelationshipProjectionExtractor implements ProjectionExtractorInterface
{
    /**
     * @throws ProjectionExtractionException
     */
    public function extract(AssertionSetInterface $assertionSet): ProjectionSetInterface
    {
        $projections = [];

        foreach ($assertionSet->getAssertions() as $assertion) {
            if ($assertion->getBindingType() !== 'relationship') {
                continue;
            }

            if ($assertion->getPayloadShape() !== BindingPayloadShapeEnum::AttributeList) {
                continue;
            }

            $relationshipType = $assertion->getFirstAttributeValue('type');
            $subject = $assertion->getFirstAttributeValue('subject');
            $object = $assertion->getFirstAttributeValue('object');

            if ($relationshipType === null || $subject === null || $object === null) {
                continue;
            }

            try {
                $projections[] = new RelationshipProjection(
                    relationshipType: $relationshipType,
                    subject: $subject,
                    object: $object,
                    label: $assertion->getLabel(),
                    originatingAssertion: $assertion,
                );
            } catch (InvalidProjectionException $exception) {
                throw new ProjectionExtractionException(
                    sprintf(
                        'Failed to extract relationship projection: %s',
                        $exception->getMessage(),
                    ),
                    previous: $exception,
                );
            }
        }

        return new ProjectionSet($projections);
    }
}
