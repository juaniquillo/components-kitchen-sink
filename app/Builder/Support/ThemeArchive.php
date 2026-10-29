<?php

declare(strict_types=1);

namespace App\Builder\Support;

use RuntimeException;
use ZipArchive;

/**
 * Packs the themes of a workspace into a zip.
 *
 * The entries carry the path a theme file has in a Laravel application, so
 * unzipping the archive at the root of a project puts every file where the
 * local theme manager reads it. The component is not part of the archive:
 * the builder has no way of knowing where the user will paste it.
 */
final class ThemeArchive
{
    /**
     * The directory a theme file lives in, relative to the project root.
     */
    public const DIRECTORY = 'resources/views/_themes/tailwind';

    /**
     * What the archive says about itself.
     *
     * The archive is opened on its own, away from the builder, so the note
     * has to carry everything the user needs to place the files.
     */
    private const README = <<<'TXT'
Theme files
===========

The archive holds one file per theme group, under the path the file has in a
Laravel application:

    resources/views/_themes/tailwind/<group>.blade.php

To add them to a project, unzip the archive at the root of the project, or
drag the resources folder into it. A file with the same name as one that is
already there replaces it, so look at the groups you share with the project
before you do.

The component itself is not in the archive: copy it from the Generated code
panel of the builder, and paste it wherever it belongs.

TXT;

    /**
     * Write the themes into a zip and return its path.
     *
     * The caller owns the file and is expected to send it and delete it.
     *
     * @param  array<string, array<string, string>>  $themes
     */
    public function write(array $themes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'builder-themes-');

        if ($path === false) {
            throw new RuntimeException('The archive could not be created.');
        }

        $archive = new ZipArchive;

        if ($archive->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('The archive could not be opened.');
        }

        $archive->addFromString('README.txt', self::README);

        $generator = new PhpCodeGenerator($themes);

        foreach (array_keys($themes) as $group) {
            $archive->addFromString(
                self::DIRECTORY.'/'.$group.'.blade.php',
                $generator->themeFile($group),
            );
        }

        $archive->close();

        return $path;
    }
}
