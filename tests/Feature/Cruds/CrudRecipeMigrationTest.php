<?php

declare(strict_types=1);

use App\Cruds\Squema\Flux\Inputs\FluxYearFactory;
use App\Cruds\Squema\InputGroup\Inputs\FavoriteAnimalFactory;
use App\Cruds\Squema\Simple\Inputs\CitiesFactory;
use App\Cruds\Squema\Simple\Inputs\ContactByFactory;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;
use Juaniquillo\InputComponentAction\Groups\InputLabelErrorGroup;
use Juaniquillo\InputComponentAction\Recipes\RadioComponentRecipe;
use Juaniquillo\InputComponentAction\Recipes\SelectOptionComponentRecipe;

/**
 * Find the recipe of a given class on an input.
 */
function recipeOf(InputInterface $input, string $class): ?object
{
    foreach ($input->getRecipes() as $recipe) {
        if ($recipe instanceof $class) {
            return $recipe;
        }
    }

    return null;
}

it('builds contact_by radios with the radio recipe', function () {
    $radios = ContactByFactory::radio();

    expect($radios)->toHaveCount(3);

    $values = [];

    foreach ($radios as $radio) {
        $recipe = recipeOf($radio, RadioComponentRecipe::class);

        expect($recipe)->toBeInstanceOf(RadioComponentRecipe::class);

        $values[] = $recipe->getInputValue();

        expect($recipe->getInputGroup())->toBeInstanceOf(InputLabelErrorGroup::class);
    }

    expect($values)->toBe(['by_text', 'by_email', 'by_phone']);
});

it('builds favorite animal radios with the radio recipe and the custom wrapper', function () {
    $radios = FavoriteAnimalFactory::radioBoxes();

    expect($radios)->toHaveCount(3);

    $values = [];

    foreach ($radios as $radio) {
        $recipe = recipeOf($radio, RadioComponentRecipe::class);

        expect($recipe)->toBeInstanceOf(RadioComponentRecipe::class);

        $values[] = $recipe->getInputValue();

        // The custom div wrapper survives: the recipe only fills in the
        // input type it does not supply itself.
        expect($recipe->getComponentBag()?->getInputType())->toBe(ComponentEnum::RADIO_INPUT);
        expect($recipe->getInputGroup())->toBeInstanceOf(InputLabelErrorGroup::class);
    }

    expect($values)->toBe(['horse_wc', 'snake_wc', 'dog_wc']);
});

it('builds city options with the select option recipe', function () {
    $options = CitiesFactory::options();

    $recipes = [];

    foreach ($options as $option) {
        $recipe = recipeOf($option, SelectOptionComponentRecipe::class);

        expect($recipe)->toBeInstanceOf(SelectOptionComponentRecipe::class);

        $recipes[] = $recipe;
    }

    // The options share one duplicated name, so the collection holds five.
    expect($recipes)->toHaveCount(5);

    // The first "Choose..." option keeps its empty value.
    expect($recipes[0]->getInputValue())->toBe('');
    expect($recipes[1]->getInputValue())->toBe('new_york');
});

it('keeps the flux select.option type on year options', function () {
    $options = FluxYearFactory::options();

    $recipes = [];

    foreach ($options as $option) {
        $recipe = recipeOf($option, SelectOptionComponentRecipe::class);

        expect($recipe)->toBeInstanceOf(SelectOptionComponentRecipe::class);

        $recipes[] = $recipe;
    }

    expect($recipes)->toHaveCount(3);

    expect($recipes[0]->getInputValue())->toBe('');

    foreach ($recipes as $recipe) {
        $type = $recipe->getComponentBag()?->getInputType();

        // The recipe preserves the custom Flux type instead of replacing
        // it with the base OPTION case; v0.2.0+ resolves the
        // 'select.option' string to its FluxComponentEnum case, which
        // renders the same value.
        expect($type)->toBeInstanceOf(FluxComponentEnum::class)
            ->and($type instanceof BackedEnum ? $type->value : $type)->toBe('select.option');
    }
});

it('renders the migrated schemas with their names and values intact', function () {
    $response = $this->get('/cruds');

    $response->assertOk();

    // Radios inherit the parent input's name.
    $response->assertSee('name="contact_by"', false);
    $response->assertSee('by_text');
    $response->assertSee('by_email');
    $response->assertSee('by_phone');

    $response->assertSee('name="favorite_animal_wc"', false);
    $response->assertSee('required', false);
    $response->assertSee('horse_wc');

    // Selects keep every option value, including the empty "Choose...".
    $response->assertSee('value=""', false);
    $response->assertSee('Choose...');
    $response->assertSee('San Francisco');
});
