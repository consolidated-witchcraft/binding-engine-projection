<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;

use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionSet;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\Interfaces\ProjectionSetSerializerInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\ProjectionSetArraySerializer;

it('implements the projection set serializer interface', function () {
    $serializer = new ProjectionSetArraySerializer();

    expect($serializer)->toBeInstanceOf(ProjectionSetSerializerInterface::class);
});

it('serializes an empty projection set to an empty list', function () {
    $serializer = new ProjectionSetArraySerializer();

    $projectionSet = new ProjectionSet([]);

    expect($serializer->serialize($projectionSet))->toBe([]);
});

it('serializes a projection set to a list of serialized projections', function () {
    $serializer = new ProjectionSetArraySerializer();

    $entityAssertion = makeProjectionSerializationEntityAssertion();
    $relationshipAssertion = makeProjectionSerializationRelationshipAssertion();

    $entityProjection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $entityAssertion,
    );

    $relationshipProjection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: 'father of',
        originatingAssertion: $relationshipAssertion,
    );

    $projectionSet = new ProjectionSet([
        $entityProjection,
        $relationshipProjection,
    ]);

    $serialized = $serializer->serialize($projectionSet);

    expect($serialized)->toHaveCount(2)
        ->and($serialized[0]['projectionType'])->toBe('entity')
        ->and($serialized[0]['projectionKey'])->toBe('entity:person:jane-austen')
        ->and($serialized[1]['projectionType'])->toBe('relationship')
        ->and($serialized[1]['projectionKey'])->toBe('relationship:parent-of:george-austen:jane-austen');
});
