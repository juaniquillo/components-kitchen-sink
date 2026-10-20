<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Builder Storage
    |--------------------------------------------------------------------------
    |
    | Where the builder saves composed components as flat JSON files. The
    | "local" disk points at storage/app/private, which is already excluded
    | from version control by storage/app/private/.gitignore.
    |
    */

    'disk' => 'local',

    'directory' => 'builder',

    'extension' => 'json',

    /*
    |--------------------------------------------------------------------------
    | On-disk format version
    |--------------------------------------------------------------------------
    |
    | Bumped whenever the shape of a saved composition changes, so that older
    | files can be migrated instead of silently misread.
    |
    */

    'version' => 1,

];
