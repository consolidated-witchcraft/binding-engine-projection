<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces\ProjectionSerializerInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\ProjectionArraySerializer;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeProjectionSerializationSourceContext(): SourceContext
{
    return new SourceContext(
        sourceId: 'worldbook',
        documentId: '01JV7M9K6J0V8V3V5S2N6X4M1Q',
        revisionId: '01JV7MB3H3H4X9R8K7C2W1F5ZP',
        vocabularyIdentifier: 'test-vocabulary',
        vocabularyVersion: '0.1.0',
    );
}

function makeProjectionSerializationEntityAssertion(): Assertion
{
    return new Assertion(
        bindingType: 'person',
        payloadShape: BindingPayloadShapeEnum::Shorthand,
        shorthandValue: 'jane-austen',
        attributes: [],
        label: 'Jane Austen',
        raw: '@person[jane-austen](Jane Austen)',
        sourceSpan: new SourceSpan(0, 33),
        sourceContext: makeProjectionSerializationSourceContext(),
    );
}

function makeProjectionSerializationRelationshipAssertion(): Assertion
{
    return new Assertion(
        bindingType: 'relationship',
        payloadShape: BindingPayloadShapeEnum::AttributeList,
        shorthandValue: null,
        attributes: [
            'type' => ['parent-of'],
            'subject' => ['george-austen'],
            'object' => ['jane-austen'],
        ],
        label: 'father of',
        raw: '@relationship[type:parent-of, subject:george-austen, object:jane-austen](father of)',
        sourceSpan: new SourceSpan(0, 84),
        sourceContext: makeProjectionSerializationSourceContext(),
    );
}

it('implements the projection serializer interface', function () {
    $serializer = new ProjectionArraySerializer();

    expect($serializer)->toBeInstanceOf(ProjectionSerializerInterface::class);
});

it('serializes an entity projection to an array', function () {
    $serializer = new ProjectionArraySerializer();

    $assertion = makeProjectionSerializationEntityAssertion();

    $projection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $assertion,
    );

    expect($serializer->serialize($projection))->toBe([
        'projectionType' => 'entity',
        'projectionKey' => 'entity:person:jane-austen',
        'entityType' => 'person',
        'identifier' => 'jane-austen',
        'label' => 'Jane Austen',
        'originatingAssertion' => [
            'bindingType' => 'person',
            'payloadShape' => 'shorthand',
            'shorthandValue' => 'jane-austen',
            'attributes' => [],
            'label' => 'Jane Austen',
            'raw' => '@person[jane-austen](Jane Austen)',
            'sourceSpan' => [
                'start' => 0,
                'end' => 33,
            ],
            'sourceContext' => [
                'sourceId' => 'worldbook',
                'documentId' => '01JV7M9K6J0V8V3V5S2N6X4M1Q',
                'revisionId' => '01JV7MB3H3H4X9R8K7C2W1F5ZP',
                'vocabularyIdentifier' => 'test-vocabulary',
                'vocabularyVersion' => '0.1.0',
            ],
        ],
    ]);
});

it('serializes an entity projection without a label', function () {
    $serializer = new ProjectionArraySerializer();

    $assertion = makeProjectionSerializationEntityAssertion();

    $projection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: null,
        originatingAssertion: $assertion,
    );

    $serialized = $serializer->serialize($projection);

    expect($serialized['label'])->toBeNull();
});

it('serializes a relationship projection to an array', function () {
    $serializer = new ProjectionArraySerializer();

    $assertion = makeProjectionSerializationRelationshipAssertion();

    $projection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: 'father of',
        originatingAssertion: $assertion,
    );

    expect($serializer->serialize($projection))->toBe([
        'projectionType' => 'relationship',
        'projectionKey' => 'relationship:parent-of:george-austen:jane-austen',
        'relationshipType' => 'parent-of',
        'subject' => 'george-austen',
        'object' => 'jane-austen',
        'label' => 'father of',
        'originatingAssertion' => [
            'bindingType' => 'relationship',
            'payloadShape' => 'attribute_list',
            'shorthandValue' => null,
            'attributes' => [
                'type' => ['parent-of'],
                'subject' => ['george-austen'],
                'object' => ['jane-austen'],
            ],
            'label' => 'father of',
            'raw' => '@relationship[type:parent-of, subject:george-austen, object:jane-austen](father of)',
            'sourceSpan' => [
                'start' => 0,
                'end' => 84,
            ],
            'sourceContext' => [
                'sourceId' => 'worldbook',
                'documentId' => '01JV7M9K6J0V8V3V5S2N6X4M1Q',
                'revisionId' => '01JV7MB3H3H4X9R8K7C2W1F5ZP',
                'vocabularyIdentifier' => 'test-vocabulary',
                'vocabularyVersion' => '0.1.0',
            ],
        ],
    ]);
});

it('serializes a relationship projection without a label', function () {
    $serializer = new ProjectionArraySerializer();

    $assertion = makeProjectionSerializationRelationshipAssertion();

    $projection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: null,
        originatingAssertion: $assertion,
    );

    $serialized = $serializer->serialize($projection);

    expect($serialized['label'])->toBeNull();
});
