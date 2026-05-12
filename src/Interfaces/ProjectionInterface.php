<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionKey;

interface ProjectionInterface
{
    public function getProjectionType(): string;

    public function getOriginatingAssertion(): AssertionInterface;

    public function getProjectionKey(): ProjectionKey;
}
