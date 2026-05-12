<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\AssertionSet;
use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\CompositeProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionExtractorInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionSet;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeCompositeProjectionExtractorSourceContext(): SourceContext
{
    return new SourceContext(
        sourceId: 'worldbook',
        documentId: '01JV7M9K6J0V8V3V5S2N6X4M1Q',
        revisionId: '01JV7MB3H3H4X9R8K7C2W1F5ZP',
        vocabularyIdentifier: 'test-vocabulary',
        vocabularyVersion: '0.1.0',
    );
}

function makeCompositeEntityAssertion(): Assertion
{
    return new Assertion(
        bindingType: 'person',
        payloadShape: BindingPayloadShapeEnum::Shorthand,
        shorthandValue: 'jane-austen',
        attributes: [],
        label: 'Jane Austen',
        raw: '@person[jane-austen](Jane Austen)',
        sourceSpan: new SourceSpan(0, 33),
        sourceContext: makeCompositeProjectionExtractorSourceContext(),
    );
}

function makeCompositeRelationshipAssertion(): Assertion
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
        label: 'George Austen was Jane Austen’s father',
        raw: '@relationship[type:parent-of, subject:george-austen, object:jane-austen]',
        sourceSpan: new SourceSpan(0, 75),
        sourceContext: makeCompositeProjectionExtractorSourceContext(),
    );
}

it('implements the projection extractor interface', function () {
    $extractor = new CompositeProjectionExtractor([]);

    expect($extractor)->toBeInstanceOf(ProjectionExtractorInterface::class);
});

it('returns an empty projection set when no extractors are registered', function () {
    $extractor = new CompositeProjectionExtractor([]);

    $projectionSet = $extractor->extract(
        new AssertionSet([
            makeCompositeEntityAssertion(),
            makeCompositeRelationshipAssertion(),
        ]),
    );

    expect($projectionSet)->toBeInstanceOf(ProjectionSet::class)
        ->and($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0)
        ->and($projectionSet->getProjections())->toBe([]);
});

it('combines projections from multiple extractors', function () {
    $extractor = new CompositeProjectionExtractor([
        new EntityProjectionExtractor(),
        new RelationshipProjectionExtractor(),
    ]);

    $entityAssertion = makeCompositeEntityAssertion();
    $relationshipAssertion = makeCompositeRelationshipAssertion();

    $projectionSet = $extractor->extract(
        new AssertionSet([
            $entityAssertion,
            $relationshipAssertion,
        ]),
    );

    expect($projectionSet->count())->toBe(2);

    $projections = $projectionSet->getProjections();

    expect($projections[0])->toBeInstanceOf(EntityProjection::class)
        ->and($projections[0]->getOriginatingAssertion())->toBe($entityAssertion)
        ->and($projections[1])->toBeInstanceOf(RelationshipProjection::class)
        ->and($projections[1]->getOriginatingAssertion())->toBe($relationshipAssertion);
});

it('preserves extractor order when combining projection sets', function () {
    $entityAssertion = makeCompositeEntityAssertion();
    $relationshipAssertion = makeCompositeRelationshipAssertion();

    $extractor = new CompositeProjectionExtractor([
        new RelationshipProjectionExtractor(),
        new EntityProjectionExtractor(),
    ]);

    $projectionSet = $extractor->extract(
        new AssertionSet([
            $entityAssertion,
            $relationshipAssertion,
        ]),
    );

    $projections = $projectionSet->getProjections();

    expect($projections)->toHaveCount(2)
        ->and($projections[0])->toBeInstanceOf(RelationshipProjection::class)
        ->and($projections[0]->getOriginatingAssertion())->toBe($relationshipAssertion)
        ->and($projections[1])->toBeInstanceOf(EntityProjection::class)
        ->and($projections[1]->getOriginatingAssertion())->toBe($entityAssertion);
});

it('preserves projection instances returned by child extractors', function () {
    $assertion = makeCompositeEntityAssertion();

    $projection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $assertion,
    );

    $childExtractor = new class ($projection) implements ProjectionExtractorInterface {
        public function __construct(
            private readonly EntityProjection $projection,
        ) {
        }

        public function extract(
            AssertionSetInterface $assertionSet,
        ): ProjectionSetInterface {
            return new ProjectionSet([
                $this->projection,
            ]);
        }
    };

    $extractor = new CompositeProjectionExtractor([
        $childExtractor,
    ]);

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->getProjections())->toBe([$projection])
        ->and($projectionSet->first())->toBe($projection)
        ->and($projectionSet->first()?->getOriginatingAssertion())->toBe($assertion);
});
