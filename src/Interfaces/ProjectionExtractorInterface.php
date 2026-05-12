<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;

interface ProjectionExtractorInterface
{
    public function extract(AssertionSetInterface $assertionSet): ProjectionSetInterface;
}
