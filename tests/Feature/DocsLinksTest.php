<?php

declare(strict_types=1);

/**
 * @return array<int, string>
 */
function docsMarkdownFiles(): array
{
    return [base_path('README.md'), ...(glob(base_path('docs/*.md')) ?: [])];
}

it('resolves every relative link in the docs', function () {
    // A split is only as good as its links: a page moved or renamed leaves a
    // dead reference nobody notices until a reader clicks it.
    $missing = [];

    foreach (docsMarkdownFiles() as $file) {
        preg_match_all('/\[[^\]]*\]\(([^)\s]+)\)/', (string) file_get_contents($file), $matches);

        foreach ($matches[1] as $link) {
            if (str_starts_with($link, '#') || preg_match('#^[a-z][a-z0-9+.-]*:#i', $link) === 1) {
                continue;
            }

            $path = explode('#', $link, 2)[0];

            if ($path === '') {
                continue;
            }

            if (! file_exists(dirname($file).'/'.$path)) {
                $missing[] = basename($file).' → '.$link;
            }
        }
    }

    expect($missing)->toBe([]);
});

it('links every docs page from the README', function () {
    $readme = (string) file_get_contents(base_path('README.md'));

    foreach (glob(base_path('docs/*.md')) ?: [] as $file) {
        expect($readme)->toContain('docs/'.basename($file));
    }
});
