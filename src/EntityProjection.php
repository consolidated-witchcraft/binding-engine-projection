<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionInterface;

readonly class EntityProjection implements ProjectionInterface
{
    private const string PROJECTION_TYPE = 'entity';

    /**
     * @throws InvalidProjectionException
     */
    public function __construct(
        private string $entityType,
        private string $identifier,
        private ?string $label,
        private AssertionInterface $originatingAssertion,
    ) {
        $this->guard();
    }

    public function getProjectionType(): string
    {
        return self::PROJECTION_TYPE;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
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
        if (trim($this->entityType) === '') {
            throw new InvalidProjectionException(
                'Entity projection type must not be empty.',
            );
        }

        if (trim($this->identifier) === '') {
            throw new InvalidProjectionException(
                'Entity projection identifier must not be empty.',
            );
        }

        if ($this->label !== null && trim($this->label) === '') {
            throw new InvalidProjectionException(
                'Entity projection label must not be empty when provided.',
            );
        }
    }

    public function getProjectionKey(): ProjectionKey
    {
        return new ProjectionKey(
            sprintf(
                'entity:%s:%s',
                $this->entityType,
                $this->identifier,
            ),
        );
    }
}
