<?php

declare(strict_types=1);

use App\Builder\Support\ClassList;

it('allows the class names tailwind uses', function () {
    expect(ClassList::allows('bg-indigo-600'))->toBeTrue()
        ->and(ClassList::allows('dark:bg-slate-800/95'))->toBeTrue()
        ->and(ClassList::allows('w-1/2'))->toBeTrue()
        ->and(ClassList::allows('p-0.5'))->toBeTrue()
        ->and(ClassList::allows('[&>*]:rounded'))->toBeTrue()
        ->and(ClassList::allows('bg-[#ff0000]'))->toBeTrue();
});

it('refuses anything that is not a class name', function () {
    expect(ClassList::allows(''))->toBeFalse()
        ->and(ClassList::allows('bg-red-500" onclick="alert(1)'))->toBeFalse()
        ->and(ClassList::allows('bg-red-500;'))->toBeFalse()
        ->and(ClassList::allows('a<b'))->toBeFalse();
});

it('keeps only the class names that may be used', function () {
    expect(ClassList::sanitize('bg-indigo-600 text-white'))->toBe('bg-indigo-600 text-white')
        ->and(ClassList::sanitize('  bg-indigo-600   text-white  '))->toBe('bg-indigo-600 text-white')
        ->and(ClassList::sanitize('bg-indigo-600 "onload="x'))->toBe('bg-indigo-600')
        ->and(ClassList::sanitize(''))->toBe('');
});
