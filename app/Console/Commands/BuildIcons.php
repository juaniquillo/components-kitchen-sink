<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DesignSystem\Icons;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('icons:build {--path= : Directory for the icon files}')]
#[Description('Build the static theme icons into the public folder')]
class BuildIcons extends Command
{
    public function handle(): int
    {
        $directory = (string) ($this->option('path') ?: public_path('icons'));

        Icons::build($directory);

        foreach (Icons::FILES as $file) {
            $this->info("Built icon → {$directory}/{$file}");
        }

        return self::SUCCESS;
    }
}
