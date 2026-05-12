<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionSet;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeProjectionSetAssertion(): Assertion
{
    return new Assertion(
        bindingType: 'person',
        payloadShape: BindingPayloadShapeEnum::Shorthand,
        shorthandValue: 'jane-austen',
        attributes: [],
        label: 'Jane Austen',
        raw: '@person[jane-austen](Jane Austen)',
        sourceSpan: new SourceSpan(0, 33),
        sourceContext: new SourceContext(
            sourceId: 'worldbook',
            documentId: '01JV7M9K6J0V8V3V5S2N6X4M1Q',
            revisionId: '01JV7MB3H3H4X9R8K7C2W1F5ZP',
            vocabularyIdentifier: 'test-vocabulary',
            vocabularyVersion: '0.1.0',
        ),
    );
}

it('constructs correctly with no projections', function () {
    $projectionSet = new ProjectionSet([]);

    expect($projectionSet)->toBeInstanceOf(ProjectionSet::class)
        ->and($projectionSet)->toBeInstanceOf(ProjectionSetInterface::class)
        ->and($projectionSet)->toBeInstanceOf(Countable::class)
        ->and($projectionSet->getProjections())->toBe([])
        ->and($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0)
        ->and(count($projectionSet))->toBe(0)
        ->and($projectionSet->first())->toBeNull();
});

it('constructs correctly with projections', function () {
    $originatingAssertion = makeProjectionSetAssertion();

    $personProjection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $originatingAssertion,
    );

    $locationProjection = new EntityProjection(
        entityType: 'location',
        identifier: 'bath',
        label: 'Bath',
        originatingAssertion: $originatingAssertion,
    );

    $projections = [
        $personProjection,
        $locationProjection,
    ];

    $projectionSet = new ProjectionSet($projections);

    expect($projectionSet->getProjections())->toBe($projections)
        ->and($projectionSet->isEmpty())->toBeFalse()
        ->and($projectionSet->count())->toBe(2)
        ->and(count($projectionSet))->toBe(2)
        ->and($projectionSet->first())->toBe($personProjection);
});

it('filters projections by projection type while preserving instances', function () {
    $originatingAssertion = makeProjectionSetAssertion();

    $personProjection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $originatingAssertion,
    );

    $locationProjection = new EntityProjection(
        entityType: 'location',
        identifier: 'bath',
        label: 'Bath',
        originatingAssertion: $originatingAssertion,
    );

    $projectionSet = new ProjectionSet([
        $personProjection,
        $locationProjection,
    ]);

    $matches = $projectionSet->getByProjectionType('entity');

    expect($matches)->toHaveCount(2)
        ->and($matches[0])->toBe($personProjection)
        ->and($matches[1])->toBe($locationProjection)
        ->and($matches[0]->getOriginatingAssertion())->toBe($originatingAssertion)
        ->and($matches[1]->getOriginatingAssertion())->toBe($originatingAssertion)
        ->and($projectionSet->getByProjectionType('relationship'))->toBe([]);
});
