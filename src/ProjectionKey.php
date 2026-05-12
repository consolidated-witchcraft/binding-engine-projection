<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection;

use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;

readonly class ProjectionKey
{
    /**
     * @throws InvalidProjectionException
     */
    public function __construct(
        private string $value,
    ) {
        if (trim($this->value) === '') {
            throw new InvalidProjectionException(
                'Projection key must not be empty.',
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
