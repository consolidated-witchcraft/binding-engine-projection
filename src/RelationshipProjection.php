<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;

readonly class RelationshipProjection implements ProjectionInterface
{
    private const string PROJECTION_TYPE = 'relationship';

    /**
     * @throws InvalidProjectionException
     */
    public function __construct(
        private string $relationshipType,
        private string $subject,
        private string $object,
        private ?string $label,
        private AssertionInterface $originatingAssertion,
    ) {
        $this->guard();
    }

    public function getProjectionType(): string
    {
        return self::PROJECTION_TYPE;
    }

    public function getRelationshipType(): string
    {
        return $this->relationshipType;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getOriginatingAssertion(): AssertionInterface
    {
        return $this->originatingAssertion;
    }

    /**
     * @throws InvalidProjectionException
     */
    private function guard(): void
    {
        if (trim($this->relationshipType) === '') {
            throw new InvalidProjectionException(
                'Relationship projection type must not be empty.',
            );
        }

        if (trim($this->subject) === '') {
            throw new InvalidProjectionException(
                'Relationship projection subject must not be empty.',
            );
        }

        if (trim($this->object) === '') {
            throw new InvalidProjectionException(
                'Relationship projection object must not be empty.',
            );
        }

        if ($this->label !== null && trim($this->label) === '') {
            throw new InvalidProjectionException(
                'Relationship projection label must not be empty when provided.',
            );
        }
    }
}
