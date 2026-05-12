<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\AssertionSet;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\RelationshipProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeRelationshipProjectionExtractorSourceContext(): SourceContext
{
    return new SourceContext(
        sourceId: 'worldbook',
        documentId: '01JV7M9K6J0V8V3V5S2N6X4M1Q',
        revisionId: '01JV7MB3H3H4X9R8K7C2W1F5ZP',
        vocabularyIdentifier: 'test-vocabulary',
        vocabularyVersion: '0.1.0',
    );
}

function makeRelationshipProjectionExtractorAssertion(
    string $relationshipType = 'parent-of',
    string $subject = 'george-austen',
    string $object = 'jane-austen',
    ?string $label = 'George Austen was Jane Austen’s father',
): Assertion {
    return new Assertion(
        bindingType: 'relationship',
        payloadShape: BindingPayloadShapeEnum::AttributeList,
        shorthandValue: null,
        attributes: [
            'type' => [$relationshipType],
            'subject' => [$subject],
            'object' => [$object],
        ],
        label: $label,
        raw: sprintf(
            '@relationship[type:%s, subject:%s, object:%s]',
            $relationshipType,
            $subject,
            $object,
        ),
        sourceSpan: new SourceSpan(0, 75),
        sourceContext: makeRelationshipProjectionExtractorSourceContext(),
    );
}

it('extracts an empty projection set from an empty assertion set', function () {
    $extractor = new RelationshipProjectionExtractor();

    $projectionSet = $extractor->extract(
        new AssertionSet([]),
    );

    expect($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0)
        ->and($projectionSet->getProjections())->toBe([]);
});

it('extracts a relationship projection from a relationship assertion', function () {
    $extractor = new RelationshipProjectionExtractor();

    $assertion = makeRelationshipProjectionExtractorAssertion();

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->count())->toBe(1);

    $projection = $projectionSet->first();

    expect($projection)->toBeInstanceOf(RelationshipProjection::class)
        ->and($projection->getProjectionType())->toBe('relationship')
        ->and($projection->getRelationshipType())->toBe('parent-of')
        ->and($projection->getSubject())->toBe('george-austen')
        ->and($projection->getObject())->toBe('jane-austen')
        ->and($projection->getLabel())->toBe('George Austen was Jane Austen’s father')
        ->and($projection->getOriginatingAssertion())->toBe($assertion);
});

it('extracts multiple relationship projections from multiple relationship assertions', function () {
    $extractor = new RelationshipProjectionExtractor();

    $parentAssertion = makeRelationshipProjectionExtractorAssertion(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: 'George Austen was Jane Austen’s father',
    );

    $siblingAssertion = makeRelationshipProjectionExtractorAssertion(
        relationshipType: 'sibling-of',
        subject: 'cassandra-austen',
        object: 'jane-austen',
        label: 'Cassandra Austen was Jane Austen’s sister',
    );

    $projectionSet = $extractor->extract(
        new AssertionSet([
            $parentAssertion,
            $siblingAssertion,
        ]),
    );

    expect($projectionSet->count())->toBe(2);

    $projections = $projectionSet->getProjections();

    expect($projections[0])->toBeInstanceOf(RelationshipProjection::class)
        ->and($projections[0]->getRelationshipType())->toBe('parent-of')
        ->and($projections[0]->getSubject())->toBe('george-austen')
        ->and($projections[0]->getObject())->toBe('jane-austen')
        ->and($projections[0]->getOriginatingAssertion())->toBe($parentAssertion)
        ->and($projections[1])->toBeInstanceOf(RelationshipProjection::class)
        ->and($projections[1]->getRelationshipType())->toBe('sibling-of')
        ->and($projections[1]->getSubject())->toBe('cassandra-austen')
        ->and($projections[1]->getObject())->toBe('jane-austen')
        ->and($projections[1]->getOriginatingAssertion())->toBe($siblingAssertion);
});

it('ignores non-relationship assertions', function () {
    $extractor = new RelationshipProjectionExtractor();

    $assertion = new Assertion(
        bindingType: 'person',
        payloadShape: BindingPayloadShapeEnum::Shorthand,
        shorthandValue: 'jane-austen',
        attributes: [],
        label: 'Jane Austen',
        raw: '@person[jane-austen](Jane Austen)',
        sourceSpan: new SourceSpan(0, 33),
        sourceContext: makeRelationshipProjectionExtractorSourceContext(),
    );

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0);
});

it('ignores shorthand relationship assertions', function () {
    $extractor = new RelationshipProjectionExtractor();

    $assertion = new Assertion(
        bindingType: 'relationship',
        payloadShape: BindingPayloadShapeEnum::Shorthand,
        shorthandValue: 'parent-of',
        attributes: [],
        label: null,
        raw: '@relationship[parent-of]',
        sourceSpan: new SourceSpan(0, 24),
        sourceContext: makeRelationshipProjectionExtractorSourceContext(),
    );

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0);
});

it(
    'ignores relationship assertions missing required projection attributes',
    function (array $attributes) {
        $extractor = new RelationshipProjectionExtractor();

        $assertion = new Assertion(
            bindingType: 'relationship',
            payloadShape: BindingPayloadShapeEnum::AttributeList,
            shorthandValue: null,
            attributes: $attributes,
            label: null,
            raw: '@relationship[...]',
            sourceSpan: new SourceSpan(0, 18),
            sourceContext: makeRelationshipProjectionExtractorSourceContext(),
        );

        $projectionSet = $extractor->extract(
            new AssertionSet([$assertion]),
        );

        expect($projectionSet->isEmpty())->toBeTrue()
            ->and($projectionSet->count())->toBe(0);
    }
)->with(function (): iterable {
    yield 'missing type' => [[
        'subject' => ['george-austen'],
        'object' => ['jane-austen'],
    ]];

    yield 'missing subject' => [[
        'type' => ['parent-of'],
        'object' => ['jane-austen'],
    ]];

    yield 'missing object' => [[
        'type' => ['parent-of'],
        'subject' => ['george-austen'],
    ]];
});

it('uses the first value when projection attributes are repeated', function () {
    $extractor = new RelationshipProjectionExtractor();

    $assertion = new Assertion(
        bindingType: 'relationship',
        payloadShape: BindingPayloadShapeEnum::AttributeList,
        shorthandValue: null,
        attributes: [
            'type' => ['parent-of', 'guardian-of'],
            'subject' => ['george-austen', 'cassandra-austen'],
            'object' => ['jane-austen', 'henry-austen'],
        ],
        label: null,
        raw: '@relationship[type:parent-of, type:guardian-of, subject:george-austen, subject:cassandra-austen, object:jane-austen, object:henry-austen]',
        sourceSpan: new SourceSpan(0, 135),
        sourceContext: makeRelationshipProjectionExtractorSourceContext(),
    );

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->count())->toBe(1);

    $projection = $projectionSet->first();

    expect($projection)->toBeInstanceOf(RelationshipProjection::class)
        ->and($projection->getRelationshipType())->toBe('parent-of')
        ->and($projection->getSubject())->toBe('george-austen')
        ->and($projection->getObject())->toBe('jane-austen');
});

it('preserves provenance through the originating assertion', function () {
    $extractor = new RelationshipProjectionExtractor();

    $assertion = makeRelationshipProjectionExtractorAssertion();

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    $projection = $projectionSet->first();

    expect($projection)->toBeInstanceOf(RelationshipProjection::class)
        ->and($projection->getOriginatingAssertion())->toBe($assertion)
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getSourceId())->toBe('worldbook')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getDocumentId())->toBe('01JV7M9K6J0V8V3V5S2N6X4M1Q')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getRevisionId())->toBe('01JV7MB3H3H4X9R8K7C2W1F5ZP')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getVocabularyIdentifier())->toBe('test-vocabulary')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getVocabularyVersion())->toBe('0.1.0');
});
