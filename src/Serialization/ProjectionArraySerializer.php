<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Serialization;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces\ProjectionSerializerInterface;

final readonly class ProjectionArraySerializer implements ProjectionSerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(ProjectionInterface $projection): array
    {
        if ($projection instanceof EntityProjection) {
            return $this->serializeEntityProjection($projection);
        }

        if ($projection instanceof RelationshipProjection) {
            return $this->serializeRelationshipProjection($projection);
        }

        return [
            'projectionType' => $projection->getProjectionType(),
            'projectionKey' => $projection->getProjectionKey()->getValue(),
            'originatingAssertion' => $this->serializeAssertion(
                $projection->getOriginatingAssertion(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEntityProjection(EntityProjection $projection): array
    {
        return [
            'projectionType' => $projection->getProjectionType(),
            'projectionKey' => $projection->getProjectionKey()->getValue(),
            'entityType' => $projection->getEntityType(),
            'identifier' => $projection->getIdentifier(),
            'label' => $projection->getLabel(),
            'originatingAssertion' => $this->serializeAssertion(
                $projection->getOriginatingAssertion(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRelationshipProjection(RelationshipProjection $projection): array
    {
        return [
            'projectionType' => $projection->getProjectionType(),
            'projectionKey' => $projection->getProjectionKey()->getValue(),
            'relationshipType' => $projection->getRelationshipType(),
            'subject' => $projection->getSubject(),
            'object' => $projection->getObject(),
            'label' => $projection->getLabel(),
            'originatingAssertion' => $this->serializeAssertion(
                $projection->getOriginatingAssertion(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAssertion(AssertionInterface $assertion): array
    {
        $sourceSpan = $assertion->getSourceSpan();
        $sourceContext = $assertion->getSourceContext();

        return [
            'bindingType' => $assertion->getBindingType(),
            'payloadShape' => $assertion->getPayloadShape()->value,
            'shorthandValue' => $assertion->getShorthandValue(),
            'attributes' => $assertion->getAttributes(),
            'label' => $assertion->getLabel(),
            'raw' => $assertion->getRaw(),
            'sourceSpan' => [
                'start' => $sourceSpan->getStart(),
                'end' => $sourceSpan->getEnd(),
            ],
            'sourceContext' => [
                'sourceId' => $sourceContext->getSourceId(),
                'documentId' => $sourceContext->getDocumentId(),
                'revisionId' => $sourceContext->getRevisionId(),
                'vocabularyIdentifier' => $sourceContext->getVocabularyIdentifier(),
                'vocabularyVersion' => $sourceContext->getVocabularyVersion(),
            ],
        ];
    }
}
