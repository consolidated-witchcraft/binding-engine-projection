# AGENTS.md — BindingEngine Projection Library

## README.md

The README.md contains valuable information about the structure, responsibilities and architectural boundaries of this project and MUST be consulted, read and followed.

---

# Purpose

This repository contains the semantic projection layer for the Consolidated Witchcraft BindingEngine ecosystem.

The responsibility of this package is:

- transforming semantic assertions into structured semantic projections
- preserving provenance and assertion traceability
- producing deterministic projection structures suitable for graph-oriented systems
- exposing semantic structures for downstream indexing, traversal and inference systems

This package MUST NOT:
- perform inference
- resolve canon
- reconcile contradictions
- merge entities
- determine authoritative truth
- silently discard provenance information
- impose a storage backend
- couple projection structures to application-specific concerns

This package exists to faithfully project semantic structures from authored assertions.

---

# Architectural Principles

## Projections Are Derived Structures, Not Truth

Projections represent:
> “This semantic structure can be derived from these authored assertions.”

They do NOT represent:
> “This structure is objectively or canonically true.”

Multiple conflicting projections may coexist simultaneously.

Conflict resolution belongs to downstream systems.

---

## Provenance Is Mandatory

Projection structures MUST preserve provenance information.

This includes:
- originating assertions
- source document identifiers
- source revision identifiers
- source spans
- vocabulary identifiers
- vocabulary versions

Downstream systems MUST be able to determine:
- which assertion produced a projection
- which vocabulary version shaped its semantics
- which source text ultimately produced it

Loss of provenance is considered a serious architectural failure.

---

## Deterministic Projection

Projection MUST be deterministic.

Given:
- identical assertions
- identical vocabulary context
- identical projection configuration

the same projections MUST always be produced.

Avoid:
- hidden heuristics
- non-deterministic ordering
- implicit semantic interpretation
- runtime mutation affecting projection output

Projection should remain transparent and reproducible.

---

## Immutable Data Structures

Projection objects SHOULD be immutable.

Prefer:
- readonly classes
- value objects
- constructor validation
- immutable collections

Avoid:
- setters
- mutable state
- hidden caches affecting semantic output
- side-effect-driven projection logic

---

## Explicitness Over Implicit Behaviour

This package prioritises:
- deterministic transforms
- explicit semantic modelling
- transparent projection flow

Avoid:
- magical graph synthesis
- hidden relationship generation
- implicit entity reconciliation
- heuristic projection behaviour

If semantic knowledge is inferred rather than directly projected, it belongs in a downstream inference layer.

---

# Repository Standards

## PHP Standards

- `declare(strict_types=1);` is mandatory
- PHPStan MUST pass at maximum configured level
- `treatPhpDocTypesAsCertain: true` is enforced
- All public APIs MUST be fully typed
- Array shapes MUST be documented where appropriate
- Prefer immutable value objects over associative arrays

---

## Exceptions

Exceptions MUST:
- be domain-specific
- preserve contextual information
- preserve previous exceptions where appropriate

Never throw:
- `\Exception`
- `\RuntimeException`
- `\Throwable`

except at application boundaries.

Projection failures should expose:
- projection type
- originating assertion context
- semantic failure reason

where possible.

---

## Testing Standards

All behaviour MUST be covered by tests.

Tests SHOULD:
- validate successful projection paths
- validate deterministic projection output
- validate provenance preservation
- validate filtering behaviour
- validate projection identity preservation
- validate edge cases

Tests MUST:
- assert exact exception types
- assert exact error messages where stable
- avoid hidden coupling between test cases
- avoid order-dependent assumptions unless ordering is contractual

Boundary tests are required for:
- provenance handling
- deterministic projection output
- projection filtering
- duplicate handling
- projection identity preservation

---

## Provenance Handling

Projection provenance is first-class system data.

When introducing new projection types or extraction paths:
- originating assertions MUST remain attached
- provenance MUST remain intact
- source spans MUST remain accurate
- vocabulary context MUST remain attached

Projection structures without provenance are considered invalid architecture.

---

## Projection vs Inference

Keep projection and inference strictly separated.

This repository projects:
- explicit semantic structures derived from assertions

It does NOT:
- derive new semantic facts
- determine causality
- resolve contradictions
- reconcile entities
- determine canonical truth

Do not introduce inference behaviour into projection code.

---

## Storage Neutrality

Projection structures MUST remain storage-neutral.

Do not couple projections to:
- specific databases
- graph engines
- ORM implementations
- persistence frameworks

Projection output should remain portable between:
- in-memory graphs
- relational systems
- graph databases
- search indexes
- custom application layers

---

## Vocabulary Compatibility

Projection semantics depend upon vocabulary semantics.

Code MUST assume:
- vocabularies evolve over time
- projection meaning may vary between versions
- downstream migration systems may exist

Never assume:
- vocabulary semantics are timeless
- projection structures are globally stable without vocabulary context

Vocabulary version context MUST remain attached to projections.

---

## Preferred Design Style

Prefer:
- composition over inheritance
- small focused projection services
- immutable DTOs/value objects
- explicit constructor validation
- deterministic transformations
- storage-neutral abstractions

Avoid:
- service locators
- hidden global state
- reflection-heavy behaviour
- runtime mutation
- storage-specific assumptions
- implicit semantic magic

---

## Commit Standards

Commits MUST:
- pass the full test suite
- pass PHPStan
- preserve deterministic projection behaviour
- preserve provenance guarantees
- preserve storage neutrality

Do not commit:
- failing tests
- partially implemented projection logic
- dead code
- debugging artefacts
- hidden inference behaviour

---

## Long-Term Direction

This package is intended to become:
- stable
- deterministic
- provenance-safe
- storage-neutral
- infrastructure-grade

Optimise for:
- correctness
- traceability
- semantic clarity
- maintainability
- deterministic behaviour

over:
- convenience
- hidden abstraction
- premature optimisation
- cleverness

---

## Coding Standards

Coding standards are contained within the `./codingstandards/` subdirectory and MUST be followed.