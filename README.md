# Binding Engine Projection

A provenance-aware projection layer for the Consolidated Witchcraft BindingEngine ecosystem.

Binding Engine Projection transforms semantic assertions into structured semantic projections suitable for graph construction, indexing, traversal and downstream reasoning systems.

## Purpose

The Binding Engine parser answers:
> “What syntactically exists in this document?”

The vocabulary layer answers:
> “Is this binding semantically valid under this vocabulary?”

Binding Assertions answers:
> “What claims does this document make?”

Binding Projection answers:
> “What semantic structures do those claims describe?”

This package acts as the bridge between semantic assertions and graph-oriented semantic structures.

## Status

Early development.

The API should be considered unstable until 1.0.0.

## Installation

```bash
composer require consolidated-witchcraft/binding-engine-projection
```

## Conceptual Overview

Given an assertion describing a person entity:

```text
Assertion
├── bindingType: person
├── payloadShape: shorthand
├── shorthandValue: jane-austen
├── label: Jane Austen
└── provenance: (...)
```

The projection layer may produce:

```text
EntityProjection
├── entityType: person
├── identifier: jane-austen
├── label: Jane Austen
├── originatingAssertion: (...)
└── provenance: (...)
```

Or a relationship assertion:

```text
Assertion
├── bindingType: relationship
├── attributes:
│   ├── type = parent_of
│   ├── subject = george-austen
│   └── object = jane-austen
└── provenance: (...)
```

May produce:

```text
RelationshipProjection
├── relationshipType: parent_of
├── from: george-austen
├── to: jane-austen
├── originatingAssertion: (...)
└── provenance: (...)
```

Projections are returned as immutable `ProjectionSet` collections.

## Design Goals

### Provenance First

Every projection preserves:
- originating assertion identity
- source document identity
- revision identity
- vocabulary identity/version
- source spans

Downstream systems should always be able to answer:
> “Which authored claim produced this structure?”

### Deterministic Projection

Projection is intentionally deterministic.

Given the same validated assertions and vocabulary context, the same projections should always be produced.

Projection does not:
- infer new knowledge
- resolve conflicts
- choose canon
- merge entities
- reconcile contradictions

Those concerns belong to downstream systems.

### Vocabulary-Aware

Projection occurs against assertions already validated against a specific vocabulary version.

This allows projection logic to safely reason about:
- binding semantics
- attribute meanings
- payload structures
- projection compatibility
- vocabulary evolution

### Graph-Oriented, Storage-Neutral

This package produces semantic projections suitable for graph-oriented systems without imposing a specific storage model.

Projection output may be consumed by:
- in-memory graphs
- relational systems
- graph databases
- search indexes
- inference engines
- custom application layers

## Example Workflow

```text
Markdown Document
↓
Parser
↓
AST
↓
Vocabulary Validator
↓
Assertion Extractor
↓
Assertion Set
↓
Projection Extractor
↓
Projection Set
↓
Inference / Graph Construction / Indexing
```

## Planned Components

### Projection Extractor

Transforms assertion sets into projection sets.

### Projection Set

Immutable collection of semantic projections.

### Projection Types

Structured semantic projections such as:
- entity projections
- relationship projections
- event projections
- attribute projections
- reference projections

### Projection Provenance

Projection structures retain:
- originating assertion references
- source context
- source spans
- vocabulary context

## Philosophy

Binding Engine Projection treats semantic structures as derived representations of authored claims rather than canonical truth.

This distinction is important.

Multiple assertions may produce:
- conflicting projections
- overlapping structures
- competing semantic interpretations

The role of this package is to faithfully project semantic structures — not to decide which representation is authoritative.

## Usage

```php
<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Assertions\AstAssertionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Assertions\SourceContext;
use ConsolidatedWitchcraft\BindingEngine\Parser\Parser;
use ConsolidatedWitchcraft\BindingEngine\Projection\AstProjectionExtractor;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Validator;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\JsonVocabularyLoader;

$parser = new Parser();
$vocabularyLoader = new JsonVocabularyLoader();
$assertionExtractor = new AstAssertionExtractor();
$projectionExtractor = new AstProjectionExtractor();

$source = '@person[jane-austen](Jane Austen)';

$vocabulary = $vocabularyLoader->load(
    file_get_contents(__DIR__ . '/vocabulary.json'),
);

$parseResult = $parser->parse($source);

if ($parseResult->hasErrors()) {
    throw new ParseException('Document contains parser errors.');
}

$validator = new Validator($vocabulary);

$validationResult = $validator->validate(
    $parseResult->getDocument(),
);

if ($validationResult->hasErrors()) {
    throw new ValidationError('Document failed vocabulary validation.');
}

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

$projectionSet = $projectionExtractor->extract(
    assertionSet: $assertionSet,
);

foreach ($projectionSet->getProjections() as $projection) {
    var_dump($projection);
}
```

### Important

The projection layer assumes:
- parser validation has already succeeded
- vocabulary validation has already succeeded
- assertion extraction has already succeeded

Malformed or semantically invalid assertions should _not_ be passed into the projection layer.

## Related Packages

| Package                                                    | Responsibility                                     |
|------------------------------------------------------------|---------------------------------------------------|
| consolidated-witchcraft/binding-engine-parser              | Parses binding syntax into AST structures         |
| consolidated-witchcraft/binding-engine-vocabulary          | Defines semantic vocabulary rules                 |
| consolidated-witchcraft/binding-engine-vocabulary-loader   | Loads vocabularies from JSON definitions          |
| consolidated-witchcraft/binding-engine-assertions          | Extracts provenance-aware semantic assertions     |
| consolidated-witchcraft/binding-engine-projection          | Projects assertions into semantic structures      |

## Development

### Quality Standards

This repository enforces strict static analysis and testing requirements.

Commits should not be made without running:

```bash
composer test
composer stan
```

PHPStan is configured with:

```neon
treatPhpDocTypesAsCertain: true
```

Docblocks are therefore considered part of the public contract and must remain accurate.

### Design Principles

This package prioritises:
- immutability
- provenance preservation
- deterministic behaviour
- explicit semantic modelling
- strict typing
- predictable projection semantics

Avoid:
- hidden inference
- implicit mutation
- storage-specific assumptions
- canonical truth resolution
- application-specific coupling

## License

Licensed under the GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later).