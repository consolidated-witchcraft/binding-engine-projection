<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Projection\Exceptions\InvalidProjectionException;
use ConsolidatedWitchcraft\BindingEngine\Projection\ProjectionKey;

it('constructs correctly', function () {
    $key = new ProjectionKey('entity:person:jane-austen');

    expect($key->getValue())->toBe('entity:person:jane-austen')
        ->and((string) $key)->toBe('entity:person:jane-austen');
});

it('compares equality by value', function () {
    $first = new ProjectionKey('entity:person:jane-austen');
    $second = new ProjectionKey('entity:person:jane-austen');
    $third = new ProjectionKey('entity:person:mary-shelley');

    expect($first->equals($second))->toBeTrue()
        ->and($first->equals($third))->toBeFalse();
});

it('rejects empty values', function (string $value) {
    expect(
        fn () => new ProjectionKey($value)
    )->toThrow(
        InvalidProjectionException::class,
        'Projection key must not be empty.',
    );
})->with(function (): iterable {
    yield 'empty string' => '';
    yield 'whitespace' => '   ';
});
