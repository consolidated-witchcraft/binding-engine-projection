<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\Assertion;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\SourceSpan;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\RelationshipProjection;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

function makeRelationshipProjectionAssertion(): Assertion
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
    $originatingAssertion = makeRelationshipProjectionAssertion();

    $projection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: 'George Austen was Jane Austen’s father',
        originatingAssertion: $originatingAssertion,
    );

    expect($projection)->toBeInstanceOf(RelationshipProjection::class)
        ->and($projection)->toBeInstanceOf(ProjectionInterface::class)
        ->and($projection->getProjectionType())->toBe('relationship')
        ->and($projection->getRelationshipType())->toBe('parent-of')
        ->and($projection->getSubject())->toBe('george-austen')
        ->and($projection->getObject())->toBe('jane-austen')
        ->and($projection->getLabel())->toBe('George Austen was Jane Austen’s father')
        ->and($projection->getOriginatingAssertion())->toBe($originatingAssertion);
});

it('constructs correctly without a label', function () {
    $originatingAssertion = makeRelationshipProjectionAssertion();

    $projection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: null,
        originatingAssertion: $originatingAssertion,
    );

    expect($projection->getLabel())->toBeNull()
        ->and($projection->getOriginatingAssertion())->toBe($originatingAssertion);
});

it(
    'rejects empty relationship types',
    function (string $relationshipType) {
        expect(
            fn () => new RelationshipProjection(
                relationshipType: $relationshipType,
                subject: 'george-austen',
                object: 'jane-austen',
                label: null,
                originatingAssertion: makeRelationshipProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Relationship projection type must not be empty.',
        );
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});

it(
    'rejects empty subjects',
    function (string $subject) {
        expect(
            fn () => new RelationshipProjection(
                relationshipType: 'parent-of',
                subject: $subject,
                object: 'jane-austen',
                label: null,
                originatingAssertion: makeRelationshipProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Relationship projection subject must not be empty.',
        );
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});

it(
    'rejects empty objects',
    function (string $object) {
        expect(
            fn () => new RelationshipProjection(
                relationshipType: 'parent-of',
                subject: 'george-austen',
                object: $object,
                label: null,
                originatingAssertion: makeRelationshipProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Relationship projection object must not be empty.',
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
            fn () => new RelationshipProjection(
                relationshipType: 'parent-of',
                subject: 'george-austen',
                object: 'jane-austen',
                label: $label,
                originatingAssertion: makeRelationshipProjectionAssertion(),
            )
        )->toThrow(
            InvalidProjectionException::class,
            'Relationship projection label must not be empty when provided.',
        );
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});

it('preserves originating assertion provenance', function () {
    $originatingAssertion = makeRelationshipProjectionAssertion();

    $projection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: 'George Austen was Jane Austen’s father',
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

it('exposes a deterministic projection key', function () {
    $originatingAssertion = makeRelationshipProjectionAssertion();

    $projection = new RelationshipProjection(
        relationshipType: 'parent-of',
        subject: 'george-austen',
        object: 'jane-austen',
        label: 'father of',
        originatingAssertion: $originatingAssertion,
    );

    expect($projection->getProjectionKey()->getValue())
        ->toBe('relationship:parent-of:george-austen:jane-austen');
});
