<?php

declare(strict_types=1);

namespace App\Builder\Support;

use App\Builder\Contracts\SavedComposition;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores composed components as flat JSON files.
 *
 * There is no database: a composition is a file under the configured
 * directory, which by default lives in storage/app/private and is therefore
 * already excluded from version control.
 *
 * @phpstan-import-type Tree from \App\Builder\Contracts\BuilderNode
 */
final class CompositionRepository
{
    public function __construct(
        private readonly CompositionSerializer $serializer = new CompositionSerializer,
    ) {}

    /**
     * Every saved composition, newest first.
     *
     * @return array<int, SavedComposition>
     */
    public function all(): array
    {
        $compositions = [];

        foreach ($this->files() as $file) {
            $envelope = $this->read($file);

            if ($envelope === null) {
                continue;
            }

            $compositions[] = new SavedComposition(
                slug: $this->slugOf($file),
                name: is_string($envelope['name'] ?? null) ? $envelope['name'] : $this->slugOf($file),
                savedAt: is_string($envelope['saved_at'] ?? null) ? $envelope['saved_at'] : '',
                bytes: (int) $this->disk()->size($file),
            );
        }

        usort(
            $compositions,
            static fn (SavedComposition $a, SavedComposition $b): int => strcmp($b->savedAt, $a->savedAt),
        );

        return $compositions;
    }

    /**
     * The tree of a saved composition.
     *
     * @return Tree|null
     */
    public function find(string $slug): ?array
    {
        $slug = $this->normalise($slug);

        if ($slug === null) {
            return null;
        }

        $path = $this->path($slug);

        if (! $this->disk()->exists($path)) {
            return null;
        }

        $envelope = $this->read($path);

        if ($envelope === null) {
            return null;
        }

        return $this->serializer->tree($envelope);
    }

    /**
     * The tree and the themes of a saved composition.
     *
     * @return array{tree: Tree, themes: array<string, array<string, string>>}|null
     */
    public function findWithThemes(string $slug): ?array
    {
        $slug = $this->normalise($slug);

        if ($slug === null) {
            return null;
        }

        $path = $this->path($slug);

        if (! $this->disk()->exists($path)) {
            return null;
        }

        $envelope = $this->read($path);

        if ($envelope === null) {
            return null;
        }

        $tree = $this->serializer->tree($envelope);

        if ($tree === null) {
            return null;
        }

        return [
            'tree' => $tree,
            'themes' => $this->serializer->themes($envelope),
        ];
    }

    /**
     * Write a composition to disk.
     *
     * @param  Tree  $tree
     * @param  array<string, array<string, string>>  $themes
     */
    public function save(string $name, array $tree, array $themes = []): SavedComposition
    {
        $slug = $this->normalise($name);

        if ($slug === null) {
            throw new RuntimeException('The composition name could not be turned into a file name.');
        }

        $envelope = $this->serializer->envelope($name, $tree, $themes);

        $contents = json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($contents === false) {
            throw new RuntimeException('The composition could not be encoded.');
        }

        $path = $this->path($slug);
        $temporary = $path.'.tmp';

        $written = $this->disk()->put($temporary, $contents, ['lock' => true]);

        if ($written === false) {
            throw new RuntimeException('The composition could not be written.');
        }

        if ($this->disk()->move($temporary, $path) === false) {
            $this->disk()->delete($temporary);

            throw new RuntimeException('The composition could not be saved.');
        }

        return new SavedComposition(
            slug: $slug,
            name: $name,
            savedAt: $envelope['saved_at'],
            bytes: strlen($contents),
        );
    }

    /**
     * Delete a composition.
     */
    public function delete(string $slug): bool
    {
        $slug = $this->normalise($slug);

        if ($slug === null) {
            return false;
        }

        $path = $this->path($slug);

        if (! $this->disk()->exists($path)) {
            return false;
        }

        return $this->disk()->delete($path);
    }

    /**
     * Turn a name into a safe file name.
     *
     * The result is restricted to characters that cannot escape the
     * directory, and the path it produces is checked against the base
     * directory as a second line of defence.
     */
    public function normalise(string $name): ?string
    {
        $slug = Str::slug($name, '-');
        $slug = trim(substr($slug, 0, 64), '-');

        if ($slug === '') {
            return null;
        }

        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            return null;
        }

        $path = $this->path($slug);
        $base = $this->directory();

        if (! str_starts_with($path, $base.'/')) {
            return null;
        }

        return $slug;
    }

    /**
     * The path of a composition inside the configured directory.
     */
    private function path(string $slug): string
    {
        return $this->directory().'/'.$slug.'.'.config('builder.extension', 'json');
    }

    private function directory(): string
    {
        return trim((string) config('builder.directory', 'builder'), '/');
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('builder.disk', 'local'));
    }

    /**
     * The composition files in the directory.
     *
     * @return array<int, string>
     */
    private function files(): array
    {
        $extension = '.'.config('builder.extension', 'json');

        $files = $this->disk()->files($this->directory());

        return array_values(array_filter(
            $files,
            static fn (string $file): bool => str_ends_with($file, $extension),
        ));
    }

    /**
     * The slug a file name represents.
     */
    private function slugOf(string $file): string
    {
        $name = basename($file, '.'.config('builder.extension', 'json'));

        return $name;
    }

    /**
     * Read and decode a composition file.
     *
     * @return array<string, mixed>|null
     */
    private function read(string $path): ?array
    {
        $contents = $this->disk()->get($path);

        if ($contents === null) {
            return null;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }
}
