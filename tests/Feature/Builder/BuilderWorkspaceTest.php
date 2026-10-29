<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\WorkspaceRepository;
use App\Livewire\Builder\BuilderCanvas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The workspace file as it is on disk.
 *
 * @return array<string, mixed>
 */
function workspaceContents(string $id): array
{
    return json_decode(
        Storage::disk('local')->get(builderTestDirectory().'/'.$id.'.json'),
        true,
    );
}

/**
 * A workspace holding nothing but its root.
 */
function freshWorkspace(): string
{
    return (new WorkspaceRepository)->create(
        WorkspaceRepository::DEFAULT_NAME,
        BuilderNode::tree('n1', 'div'),
    );
}

it('offers a way to start a workspace and a way back to one', function () {
    $this->get(route('builder'))
        ->assertOk()
        ->assertSee('New workspace')
        ->assertSee('Open a workspace');
});

it('makes a workspace and goes straight to it', function () {
    $response = $this->post(route('builder.store'));

    $response->assertRedirect();

    $location = (string) $response->headers->get('Location');
    $id = WorkspaceRepository::idFromLink($location);

    expect($id)->not->toBeNull()
        ->and((new WorkspaceRepository)->exists($id))->toBeTrue()
        ->and(workspaceContents($id)['name'])->toBe(WorkspaceRepository::DEFAULT_NAME);
});

it('opens a workspace by its address', function () {
    $id = freshWorkspace();

    $this->get(route('builder.workspace', $id))
        ->assertOk()
        ->assertSee('data-builder-canvas', false)
        ->assertSee($id, false);
});

it('turns a malformed address away', function () {
    $this->get('/builder/not-a-workspace')->assertNotFound();
});

it('sends a dead link back to the page that makes them', function () {
    $this->get(route('builder.workspace', (string) Str::ulid()))
        ->assertRedirect(route('builder'))
        ->assertSessionHas('builder-notice', 'That workspace is not here any more.');
});

it('opens a workspace from a pasted link', function () {
    $id = freshWorkspace();

    $this->post(route('builder.open'), ['link' => 'https://builder.test/builder/'.$id])
        ->assertRedirect(route('builder.workspace', $id));
});

it('opens a workspace from the id at the end of a link', function () {
    $id = freshWorkspace();

    $this->post(route('builder.open'), ['link' => $id])
        ->assertRedirect(route('builder.workspace', $id));
});

it('says why a pasted link is not one', function () {
    $this->post(route('builder.open'), ['link' => 'not a link'])
        ->assertRedirect()
        ->assertSessionHas('builder-notice', 'That is not a workspace link. Paste the whole address, or the id at the end of it.');
});

it('says why a pasted link points at nothing', function () {
    $this->post(route('builder.open'), ['link' => 'https://builder.test/builder/'.Str::ulid()])
        ->assertRedirect()
        ->assertSessionHas('builder-notice', 'That workspace is not here any more.');
});

it('writes a change to the workspace file', function () {
    $id = freshWorkspace();

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('add', 'paragraph');

    expect(workspaceContents($id)['tree']['nodes'])->toHaveKey('n2');
});

it('opens the workspace the address names', function () {
    $id = freshWorkspace();

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('add', 'headers.h1')
        ->set('name', 'Landing page');

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->assertSet('name', 'Landing page')
        ->assertSet('tree.nodes.n2.type', 'headers.h1')
        ->assertSet('workspace', $id);
});

it('writes the name as it is typed', function () {
    $id = freshWorkspace();

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->set('name', 'Pricing');

    expect(workspaceContents($id)['name'])->toBe('Pricing');
});

it('leaves the file alone when a panel is opened', function () {
    // Every request writes on its way out, but only the ones that changed
    // the workspace: opening the themes panel is not a change.
    $id = freshWorkspace();

    $component = Livewire::test(BuilderCanvas::class, ['workspace' => $id]);

    $before = workspaceContents($id)['saved_at'];

    $component->call('openThemes');

    expect(workspaceContents($id)['saved_at'])->toBe($before);
});

it('writes the workspace on a save from the keyboard', function () {
    $id = freshWorkspace();

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('add', 'paragraph')
        ->call('save')
        ->assertDispatched('builder-saved');

    expect(workspaceContents($id)['tree']['nodes'])->toHaveKey('n2');
});

it('starts a shared link with no history of its own', function () {
    // The history belongs to the browser that made the changes, so a link
    // opened somewhere else starts on the work, not on someone else's undo.
    $id = freshWorkspace();

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('add', 'paragraph');

    session()->flush();

    $component = Livewire::test(BuilderCanvas::class, ['workspace' => $id]);

    expect($component->instance()->canUndo())->toBeFalse()
        ->and($component->instance()->canRedo())->toBeFalse()
        ->and($component->instance()->tree['nodes'])->toHaveKey('n2');
});

it('clears the canvas in place, and the undo brings it back', function () {
    $id = freshWorkspace();

    $component = Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('add', 'paragraph')
        ->set('name', 'Pricing')
        ->call('resetCanvas');

    $file = workspaceContents($id);

    expect($file['tree']['nodes'])->toHaveCount(1)
        ->and($file['name'])->toBe('Pricing');

    $component->call('undo');

    expect(workspaceContents($id)['tree']['nodes'])->toHaveKey('n2');
});

it('deletes the workspace and goes back to the page that makes them', function () {
    $id = freshWorkspace();

    Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('deleteWorkspace')
        ->assertRedirect(route('builder'))
        ->assertSessionHas('builder-notice', 'That workspace was deleted.');

    expect((new WorkspaceRepository)->exists($id))->toBeFalse();
});

it('starts another workspace and goes to it', function () {
    $id = freshWorkspace();

    $component = Livewire::test(BuilderCanvas::class, ['workspace' => $id])
        ->call('newWorkspace')
        ->assertRedirect();

    $newId = WorkspaceRepository::idFromLink((string) $component->effects['redirect']);

    // The workspace that was left is still there: its link is the way back.
    expect($newId)->not->toBeNull()
        ->and($newId)->not->toBe($id)
        ->and((new WorkspaceRepository)->exists($id))->toBeTrue()
        ->and((new WorkspaceRepository)->exists($newId))->toBeTrue()
        ->and(Storage::disk('local')->files(builderTestDirectory()))->toHaveCount(2);
});
