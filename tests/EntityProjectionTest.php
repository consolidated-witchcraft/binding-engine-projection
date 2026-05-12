<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\EntityProjection;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeEntityProjectionAssertion(): Assertion
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

it('constructs correctly', function () {
    $originatingAssertion = makeEntityProjectionAssertion();

    $projection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $originatingAssertion,
    );

    expect($projection)->toBeInstanceOf(EntityProjection::class)
        ->and($projection)->toBeInstanceOf(ProjectionInterface::class)
        ->and($projection->getProjectionType())->toBe('entity')
        ->and($projection->getEntityType())->toBe('person')
        ->and($projection->getIdentifier())->toBe('jane-austen')
        ->and($projection->getLabel())->toBe('Jane Austen')
        ->and($projection->getOriginatingAssertion())->toBe($originatingAssertion);
});

it('constructs correctly without a label', function () {
    $originatingAssertion = makeEntityProjectionAssertion();

    $projection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: null,
        originatingAssertion: $originatingAssertion,
    );

    expect($projection->getLabel())->toBeNull()
        ->and($projection->getOriginatingAssertion())->toBe($originatingAssertion);
});

it(
    'rejects empty entity types',
    function (string $entityType) {
        expect(
            fn () => new EntityProjection(
                entityType: $entityType,
                identifier: 'jane-austen',
                label: 'Jane Austen',
                originatingAssertion: makeEntityProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Entity projection type must not be empty.',
        );
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});

it(
    'rejects empty identifiers',
    function (string $identifier) {
        expect(
            fn () => new EntityProjection(
                entityType: 'person',
                identifier: $identifier,
                label: 'Jane Austen',
                originatingAssertion: makeEntityProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Entity projection identifier must not be empty.',
        );
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});

it(
    'rejects empty labels when provided',
    function (string $label) {
        expect(
            fn () => new EntityProjection(
                entityType: 'person',
                identifier: 'jane-austen',
                label: $label,
                originatingAssertion: makeEntityProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Entity projection label must not be empty when provided.',
        );
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});

it('preserves originating assertion provenance', function () {
    $originatingAssertion = makeEntityProjectionAssertion();

    $projection = new EntityProjection(
        entityType: 'person',
        identifier: 'jane-austen',
        label: 'Jane Austen',
        originatingAssertion: $originatingAssertion,
    );

    $sourceContext = $projection->getOriginatingAssertion()->getSourceContext();

    expect($projection->getOriginatingAssertion())->toBe($originatingAssertion)
        ->and($sourceContext->getSourceId())->toBe('worldbook')
        ->and($sourceContext->getDocumentId())->toBe('01JV7M9K6J0V8V3V5S2N6X4M1Q')
        ->and($sourceContext->getRevisionId())->toBe('01JV7MB3H3H4X9R8K7C2W1F5ZP')
        ->and($sourceContext->getVocabularyIdentifier())->toBe('test-vocabulary')
        ->and($sourceContext->getVocabularyVersion())->toBe('0.1.0');
});
