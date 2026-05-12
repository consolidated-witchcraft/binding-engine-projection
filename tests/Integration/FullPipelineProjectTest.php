<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\AstAssertionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Parser;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\CompositeProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\EntityProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\RelationshipProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Projection\Serialization\ProjectionSetArraySerializer;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Validator;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\JsonVocabularyLoader;

it('projects and serializes a validated binding document through the full pipeline', function () {
    $vocabularyJson = json_encode([
        'identifier' => 'test-vocabulary',
        'label' => 'Test Vocabulary',
        'version' => '0.1.0',
        'bindingTypes' => [
            [
                'identifier' => 'person',
                'label' => 'Person',
                'description' => 'A person entity.',
                'allowedPayloadShapes' => ['shorthand'],
                'attributes' => [],
            ],
            [
                'identifier' => 'relationship',
                'label' => 'Relationship',
                'description' => 'A relationship between two entities.',
                'allowedPayloadShapes' => ['attribute_list'],
                'attributes' => [
                    [
                        'identifier' => 'type',
                        'label' => 'Type',
                        'description' => 'The relationship type.',
                        'valueType' => 'string',
                        'required' => true,
                        'repeatable' => false,
                    ],
                    [
                        'identifier' => 'subject',
                        'label' => 'Subject',
                        'description' => 'The subject entity.',
                        'valueType' => 'string',
                        'required' => true,
                        'repeatable' => false,
                    ],
                    [
                        'identifier' => 'object',
                        'label' => 'Object',
                        'description' => 'The object entity.',
                        'valueType' => 'string',
                        'required' => true,
                        'repeatable' => false,
                    ],
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $source = <<<'MARKDOWN'
@person[jane-austen](Jane Austen)

@person[george-austen](George Austen)

@relationship[
    type: parent-of,
    subject: george-austen,
    object: jane-austen
](George Austen was Jane Austen's father)
MARKDOWN;

    $parser = new Parser();
    $vocabularyLoader = new JsonVocabularyLoader();
    $assertionExtractor = new AstAssertionExtractor();

    $projectionExtractor = new CompositeProjectionExtractor([
        new EntityProjectionExtractor(),
        new RelationshipProjectionExtractor(),
    ]);

    $serializer = new ProjectionSetArraySerializer();

    $vocabulary = $vocabularyLoader->load($vocabularyJson);

    $parseResult = $parser->parse($source);

    expect($parseResult->hasErrors())->toBeFalse();

    $validator = new Validator($vocabulary);

    $validationResult = $validator->validate(
        $parseResult->getDocument(),
    );

    expect($validationResult->hasErrors())->toBeFalse();

    $sourceContext = new SourceContext(
        sourceId: 'worldbook',
        documentId: 'doc-123',
        revisionId: 'rev-456',
        vocabularyIdentifier: $vocabulary->getIdentifier(),
        vocabularyVersion: $vocabulary->getVersion(),
    );

    $assertionSet = $assertionExtractor->extract(
        document: $parseResult->getDocument(),
        sourceContext: $sourceContext,
    );

    expect($assertionSet->count())->toBe(3);

    $projectionSet = $projectionExtractor->extract(
        assertionSet: $assertionSet,
    );

    expect($projectionSet->count())->toBe(3);

    $serialized = $serializer->serialize($projectionSet);

    expect($serialized)->toHaveCount(3)
        ->and($serialized[0]['projectionType'])->toBe('entity')
        ->and($serialized[0]['projectionKey'])->toBe('entity:person:jane-austen')
        ->and($serialized[0]['entityType'])->toBe('person')
        ->and($serialized[0]['identifier'])->toBe('jane-austen')
        ->and($serialized[0]['label'])->toBe('Jane Austen')
        ->and($serialized[0]['originatingAssertion']['sourceContext'])->toBe([
            'sourceId' => 'worldbook',
            'documentId' => 'doc-123',
            'revisionId' => 'rev-456',
            'vocabularyIdentifier' => 'test-vocabulary',
            'vocabularyVersion' => '0.1.0',
        ])
        ->and($serialized[1]['projectionType'])->toBe('entity')
        ->and($serialized[1]['projectionKey'])->toBe('entity:person:george-austen')
        ->and($serialized[1]['identifier'])->toBe('george-austen')
        ->and($serialized[2]['projectionType'])->toBe('relationship')
        ->and($serialized[2]['projectionKey'])->toBe('relationship:parent-of:george-austen:jane-austen')
        ->and($serialized[2]['relationshipType'])->toBe('parent-of')
        ->and($serialized[2]['subject'])->toBe('george-austen')
        ->and($serialized[2]['object'])->toBe('jane-austen')
        ->and($serialized[2]['label'])->toBe("George Austen was Jane Austen's father");
});
