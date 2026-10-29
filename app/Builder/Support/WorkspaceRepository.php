<?php

declare(strict_types=1);

namespace App\Builder\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores a builder workspace as a flat JSON file, named by its id.
 *
 * There is no database and no account: the id in the address is the whole
 * of what a workspace is, and the file it names is where the work lives.
 * Anyone holding the address can open it, which is what makes a workspace
 * shareable.
 *
 * @phpstan-import-type Tree from \App\Builder\Contracts\BuilderNode
 */
final class WorkspaceRepository
{
    /**
     * The name a workspace is given when it is made.
     */
    public const DEFAULT_NAME = 'Untitled workspace';

    /**
     * The longest name a workspace is allowed to keep.
     */
    private const NAME_LIMIT = 120;

    public function __construct(
        private readonly CompositionSerializer $serializer = new CompositionSerializer,
    ) {}

    /**
     * Make a workspace and write it to disk.
     *
     * The file is written straight away: the address is the only way back
     * to a workspace, so one that was handed out before its first change
     * has to be there when the user opens it.
     *
     * @param  Tree  $tree
     * @param  array<string, array<string, string>>  $themes
     */
    public function create(string $name, array $tree, array $themes = []): string
    {
        $id = (string) Str::ulid();

        $this->save($id, $name, $tree, $themes);

        return $id;
    }

    /**
     * The name, the tree and the themes of a workspace.
     *
     * @return array{name: string, tree: Tree, themes: array<string, array<string, string>>, savedAt: string}|null
     */
    public function find(string $id): ?array
    {
        if (! self::validId($id)) {
            return null;
        }

        $path = $this->path($id);

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
            'name' => $this->nameOf($envelope),
            'tree' => $tree,
            'themes' => $this->serializer->themes($envelope),
            'savedAt' => is_string($envelope['saved_at'] ?? null) ? $envelope['saved_at'] : '',
        ];
    }

    /**
     * Write a workspace, replacing the one that was there.
     *
     * @param  Tree  $tree
     * @param  array<string, array<string, string>>  $themes
     */
    public function save(string $id, string $name, array $tree, array $themes = []): void
    {
        if (! self::validId($id)) {
            throw new RuntimeException('A workspace id is a ULID, and this one is not.');
        }

        $envelope = ['id' => $id] + $this->serializer->envelope($this->nameOr($name), $tree, $themes);

        $contents = json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($contents === false) {
            throw new RuntimeException('The workspace could not be encoded.');
        }

        $path = $this->path($id);
        $temporary = $path.'.tmp';

        if ($this->disk()->put($temporary, $contents, ['lock' => true]) === false) {
            throw new RuntimeException('The workspace could not be written.');
        }

        if ($this->disk()->move($temporary, $path) === false) {
            $this->disk()->delete($temporary);

            throw new RuntimeException('The workspace could not be saved.');
        }
    }

    /**
     * Whether a workspace is there.
     */
    public function exists(string $id): bool
    {
        return self::validId($id) && $this->disk()->exists($this->path($id));
    }

    /**
     * Delete a workspace.
     */
    public function delete(string $id): bool
    {
        if (! self::validId($id)) {
            return false;
        }

        $path = $this->path($id);

        if (! $this->disk()->exists($path)) {
            return false;
        }

        return $this->disk()->delete($path);
    }

    /**
     * Whether an id is one a workspace can have.
     *
     * A ULID is the only shape the builder writes, and it is also what
     * keeps an id from naming a file outside the directory.
     */
    public static function validId(string $id): bool
    {
        return Str::isUlid($id);
    }

    /**
     * Pull the id out of a link, or take the whole thing as an id.
     */
    public static function idFromLink(string $link): ?string
    {
        $path = parse_url(trim($link), PHP_URL_PATH);

        if (! is_string($path)) {
            return null;
        }

        $id = basename(rtrim($path, '/'));

        return self::validId($id) ? $id : null;
    }

    /**
     * The name a workspace file carries.
     *
     * @param  array<string, mixed>  $envelope
     */
    private function nameOf(array $envelope): string
    {
        $name = $envelope['name'] ?? null;

        return is_string($name) && trim($name) !== '' ? $name : self::DEFAULT_NAME;
    }

    /**
     * The name a workspace is written under.
     */
    private function nameOr(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return self::DEFAULT_NAME;
        }

        return Str::limit($name, self::NAME_LIMIT, '');
    }

    private function path(string $id): string
    {
        return $this->directory().'/'.$id.'.'.config('builder.extension', 'json');
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
     * Read and decode a workspace file.
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
