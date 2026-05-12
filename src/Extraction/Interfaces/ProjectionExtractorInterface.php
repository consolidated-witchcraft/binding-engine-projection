<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Projection\Extraction\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Assertions\Interfaces\AssertionSetInterface;
use ConsolidatedWitchcraft\BindingEngine\Projection\Interfaces\ProjectionSetInterface;

interface ProjectionExtractorInterface
{
    public function extract(AssertionSetInterface $assertionSet): ProjectionSetInterface;
}
