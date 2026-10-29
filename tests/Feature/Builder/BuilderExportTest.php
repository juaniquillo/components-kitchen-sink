<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\WorkspaceRepository;
use Illuminate\Support\Str;

it('downloads the themes of a workspace as a zip', function () {
    $id = (new WorkspaceRepository)->create(
        'Hero section',
        BuilderNode::tree('n1', 'div'),
        ['action' => ['primary' => 'bg-indigo-600']],
    );

    $this->get(route('builder.export', ['workspace' => $id]))
        ->assertOk()
        ->assertDownload('hero-section.zip');
});

it('gives the archive a name of its own when the workspace has none', function () {
    $id = (new WorkspaceRepository)->create(
        '¡!?',
        BuilderNode::tree('n1', 'div'),
        ['action' => ['primary' => 'bg-indigo-600']],
    );

    $this->get(route('builder.export', ['workspace' => $id]))
        ->assertOk()
        ->assertDownload('themes.zip');
});

it('has nothing to export for a workspace that is not there', function () {
    $this->get(route('builder.export', ['workspace' => (string) Str::ulid()]))
        ->assertNotFound();
});

it('has nothing to export for an address that is not a workspace', function () {
    $this->get('/builder/not-a-workspace/export')->assertNotFound();
});
