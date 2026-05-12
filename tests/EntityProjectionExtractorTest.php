<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\AssertionSet;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeEntityProjectionExtractorSourceContext(): SourceContext
{
    return new SourceContext(
        sourceId: 'worldbook',
        documentId: '01JV7M9K6J0V8V3V5S2N6X4M1Q',
        revisionId: '01JV7MB3H3H4X9R8K7C2W1F5ZP',
        vocabularyIdentifier: 'test-vocabulary',
        vocabularyVersion: '0.1.0',
    );
}

function makeEntityProjectionExtractorAssertion(
    string $bindingType = 'person',
    string $shorthandValue = 'jane-austen',
    ?string $label = 'Jane Austen',
): Assertion {
    return new Assertion(
        bindingType: $bindingType,
        payloadShape: BindingPayloadShapeEnum::Shorthand,
        shorthandValue: $shorthandValue,
        attributes: [],
        label: $label,
        raw: sprintf('@%s[%s]', $bindingType, $shorthandValue),
        sourceSpan: new SourceSpan(0, 20),
        sourceContext: makeEntityProjectionExtractorSourceContext(),
    );
}

it('extracts an empty projection set from an empty assertion set', function () {
    $extractor = new EntityProjectionExtractor();

    $projectionSet = $extractor->extract(
        new AssertionSet([]),
    );

    expect($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0)
        ->and($projectionSet->getProjections())->toBe([]);
});

it('extracts an entity projection from a shorthand assertion', function () {
    $extractor = new EntityProjectionExtractor();

    $assertion = makeEntityProjectionExtractorAssertion();

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->count())->toBe(1);

    $projection = $projectionSet->first();

    expect($projection)->toBeInstanceOf(EntityProjection::class)
        ->and($projection->getProjectionType())->toBe('entity')
        ->and($projection->getEntityType())->toBe('person')
        ->and($projection->getIdentifier())->toBe('jane-austen')
        ->and($projection->getLabel())->toBe('Jane Austen')
        ->and($projection->getOriginatingAssertion())->toBe($assertion);
});

it('extracts multiple entity projections from multiple shorthand assertions', function () {
    $extractor = new EntityProjectionExtractor();

    $personAssertion = makeEntityProjectionExtractorAssertion(
        bindingType: 'person',
        shorthandValue: 'jane-austen',
        label: 'Jane Austen',
    );

    $locationAssertion = makeEntityProjectionExtractorAssertion(
        bindingType: 'location',
        shorthandValue: 'bath',
        label: 'Bath',
    );

    $projectionSet = $extractor->extract(
        new AssertionSet([
            $personAssertion,
            $locationAssertion,
        ]),
    );

    expect($projectionSet->count())->toBe(2);

    $projections = $projectionSet->getProjections();

    expect($projections[0])->toBeInstanceOf(EntityProjection::class)
        ->and($projections[0]->getEntityType())->toBe('person')
        ->and($projections[0]->getIdentifier())->toBe('jane-austen')
        ->and($projections[0]->getOriginatingAssertion())->toBe($personAssertion)
        ->and($projections[1])->toBeInstanceOf(EntityProjection::class)
        ->and($projections[1]->getEntityType())->toBe('location')
        ->and($projections[1]->getIdentifier())->toBe('bath')
        ->and($projections[1]->getOriginatingAssertion())->toBe($locationAssertion);
});

it('ignores attribute-list assertions', function () {
    $extractor = new EntityProjectionExtractor();

    $assertion = new Assertion(
        bindingType: 'relationship',
        payloadShape: BindingPayloadShapeEnum::AttributeList,
        shorthandValue: null,
        attributes: [
            'type' => ['parent-of'],
            'subject' => ['george-austen'],
            'object' => ['jane-austen'],
        ],
        label: null,
        raw: '@relationship[type:parent-of, subject:george-austen, object:jane-austen]',
        sourceSpan: new SourceSpan(0, 75),
        sourceContext: makeEntityProjectionExtractorSourceContext(),
    );

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    expect($projectionSet->isEmpty())->toBeTrue()
        ->and($projectionSet->count())->toBe(0);
});

it('preserves provenance through the originating assertion', function () {
    $extractor = new EntityProjectionExtractor();

    $assertion = makeEntityProjectionExtractorAssertion();

    $projectionSet = $extractor->extract(
        new AssertionSet([$assertion]),
    );

    $projection = $projectionSet->first();

    expect($projection)->toBeInstanceOf(EntityProjection::class)
        ->and($projection->getOriginatingAssertion())->toBe($assertion)
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getSourceId())->toBe('worldbook')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getDocumentId())->toBe('01JV7M9K6J0V8V3V5S2N6X4M1Q')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getRevisionId())->toBe('01JV7MB3H3H4X9R8K7C2W1F5ZP')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getVocabularyIdentifier())->toBe('test-vocabulary')
        ->and($projection->getOriginatingAssertion()->getSourceContext()->getVocabularyVersion())->toBe('0.1.0');
});
