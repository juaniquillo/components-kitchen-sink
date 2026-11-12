<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Emails\EmailCollection;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Juaniquillo\MjmlBackendComponents\Compilers\NodeProcessCompiler;

#[Signature('emails:render')]
#[Description('Render MJML email components to HTML and save as blade views')]
class RenderMjmlEmails extends Command
{
    public function handle(): int
    {
        $emails = EmailCollection::list();

        // mjml reads stdin with -i and writes html to stdout with -s.
        $compiler = new NodeProcessCompiler(['arguments' => ['mjml', '-i', '-s']]);

        $outputDir = resource_path('views/mail');

        if (! File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        foreach ($emails as $name => $class) {
            $component = $class::build();
            $html = $component->renderHtml($compiler);

            File::put("{$outputDir}/{$name}.blade.php", $html);

            $this->info("Rendered {$name} email → resources/views/mail/{$name}.blade.php");
        }

        $this->info('All emails rendered successfully.');

        return self::SUCCESS;
    }
}
