<?php

declare(strict_types=1);

it('has session lifetime set to one month', function (): void {
    expect(config('session.lifetime'))->toBe(43200);
});

it('has session expire on close disabled', function (): void {
    expect(config('session.expire_on_close'))->toBeFalse();
});

it('has session cookie set to http only', function (): void {
    expect(config('session.http_only'))->toBeTrue();
});

it('has session cookie same site set to lax', function (): void {
    expect(config('session.same_site'))->toBe('lax');
});
