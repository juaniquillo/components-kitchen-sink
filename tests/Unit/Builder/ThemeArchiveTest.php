<?php

declare(strict_types=1);

use App\Builder\Support\ThemeArchive;

/**
 * The entries and the note of an archive built from some themes.
 *
 * @param  array<string, array<string, string>>  $themes
 * @return array{files: array<string, string>, readme: string}
 */
function themeArchiveEntries(array $themes): array
{
    $path = (new ThemeArchive)->write($themes);

    $archive = new ZipArchive;

    expect($archive->open($path))->toBeTrue();

    $files = [];

    for ($index = 0; $index < $archive->numFiles; $index++) {
        $name = (string) $archive->getNameIndex($index);

        $files[$name] = (string) $archive->getFromIndex($index);
    }

    $archive->close();
    unlink($path);

    return ['files' => $files, 'readme' => $files['README.txt'] ?? ''];
}

it('writes every theme into the archive under its project path', function () {
    ['files' => $files] = themeArchiveEntries([
        'action' => ['primary' => 'bg-indigo-600 text-white'],
        'padding' => ['sm' => 'p-2'],
    ]);

    expect($files)->toHaveKeys([
        'resources/views/_themes/tailwind/action.blade.php',
        'resources/views/_themes/tailwind/padding.blade.php',
    ])
        ->and($files['resources/views/_themes/tailwind/action.blade.php'])
        ->toContain("'primary' => 'bg-indigo-600 text-white',")
        ->and($files['resources/views/_themes/tailwind/padding.blade.php'])
        ->toContain("'sm' => 'p-2',");
});

it('writes a file the theme manager can read back', function () {
    ['files' => $files] = themeArchiveEntries([
        'action' => ['primary' => 'bg-indigo-600', 'link' => 'text-blue-600 underline'],
    ]);

    $path = tempnam(sys_get_temp_dir(), 'builder-theme-');

    expect($path)->not->toBeFalse();

    file_put_contents((string) $path, $files['resources/views/_themes/tailwind/action.blade.php']);

    $themes = require (string) $path;

    unlink((string) $path);

    expect($themes)->toBe([
        'primary' => 'bg-indigo-600',
        'link' => 'text-blue-600 underline',
    ]);
});

it('says where the files go', function () {
    ['readme' => $readme] = themeArchiveEntries([]);

    expect($readme)->toContain('resources/views/_themes/tailwind/')
        ->toContain('unzip the archive at the root')
        ->toContain('drag the resources folder');
});
