---
name: input-component-action
description: Build and customize CrudAssistant input forms rendered as Laravel Backend Components with juaniquillo/input-component-action. Use when creating, reviewing, debugging, or upgrading input recipes, the five configuration bags, slot groups, labels, help text, errors, values, select and radio sub-elements, theming, or the rendered input component tree.
license: MIT
metadata:
  author: juaniquillo
---

# Input Component Action

`juaniquillo/input-component-action` turns [CrudAssistant](https://github.com/juaniquillo/crud-assistant)
inputs into renderable [Laravel Backend Components](https://github.com/juaniquillo/laravel-backend-component).
It is the rendering layer for input forms: recipes configure each slot, groups control
layout, and the action returns one backend component per input.

## When to use this skill

Use this skill when the task involves:

- Rendering CrudAssistant inputs with `InputComponentAction`.
- Customizing an input's wrapper, label, input, error, or help text.
- Adding attributes, themes, hooks, or disabled slots to an input.
- Building select options or radio/checkbox groups with sub-elements.
- Resolving compiled values, model values, or validation errors.
- Creating a custom input group or recipe.
- Upgrading code that still uses the pre-1.0 API.

For low-level component building, theming, and serialization, use the
`backend-component` skill from `juaniquillo/laravel-backend-component` instead of
duplicating it here.

## Mental model

```text
InputComponentAction
  -> InputGroupFactory(GroupContext)
    -> InputGroup (layout)
      -> SlotComposer / InputComposer
        -> BackendComponent tree
```

- The **action** owns values, errors, the model, and the defaults.
- A **recipe** customizes one input (bags, label, values, errors, group).
- A **group** decides which slots render and in what order.
- The five **slots** are `WRAPPER`, `LABEL`, `INPUT`, `ERROR`, and `HELP_TEXT`.
- The **output** is an `InputComponentOutput` containing one component per input.

## Quick start

```php
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\InputComponentAction;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

$input = new DefaultInput('name', 'Name');

$input->setRecipe(new InputComponentRecipe(
    attributeBag: (new DefaultAttributeBag)->setInputAttributes([
        'placeholder' => 'Your name',
    ]),
    helpText: 'How should we call you?',
));

$output = CrudAssistant::make([$input])->execute(
    InputComponentAction::make(
        values: ['name' => 'Alice'],
        errors: ['name' => 'This field is required.'],
    ),
);

$component = $output->inputs->get('name');
```

Render the result with Blade. Components implement `Htmlable`:

```blade
{{ $component }}
```

## Recipes

A recipe customizes how one input is rendered. Attach it with
`$input->setRecipe(...)`. Recipes are keyed by the action identifier, so create a
new `InputComponentRecipe` for each input.

```php
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Bags\DefaultDisableBag;
use Juaniquillo\InputComponentAction\Bags\DefaultHookBag;
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

$recipe = new InputComponentRecipe(
    componentBag: (new DefaultComponentBag)->setInputType(ComponentEnum::TEXTAREA),
    attributeBag: (new DefaultAttributeBag)->setInputAttributes(['rows' => 4]),
    themeBag: (new DefaultThemeBag)->setInputTheme(['inputs' => 'default']),
    hookBag: (new DefaultHookBag)->setInputHook($hook),
    disableBag: (new DefaultDisableBag)->setDisableError(),
    inputValue: fn ($input, $values, $model) => $model?->name,
    inputError: 'Optional recipe error',
    label: 'Name',
    helpText: 'Use your legal name.',
    inputGroup: new \Juaniquillo\InputComponentAction\Groups\LabelHelpTextInputErrorGroup,
    useParentValue: false,
    labelAsInputContent: false,
    valueAsInputContent: false,
    emptyLabel: false,
    checkable: false,
    selectable: false,
);
```

`InputComponentRecipe::make()` was removed. Always use `new InputComponentRecipe`.
When extending the recipe, override `configure()` to set defaults after the
constructor runs.

### Constructor options

| Option | Purpose |
| --- | --- |
| `inputGroup` | Group layout class |
| `themeManager` | Per-input theme manager |
| `componentBag` | Component classes/types per slot |
| `attributeBag` | Attributes per slot |
| `themeBag` | Themes per slot |
| `hookBag` | Hooks per slot |
| `valueBag` | Custom value manager |
| `errorBag` | Custom error manager |
| `disableBag` | Disable flags |
| `inputValue` | Value or value closure |
| `inputError` | Error or error closure |
| `label` | Label or label closure |
| `useParentValue` | Compare against the parent input value |
| `labelAsInputContent` | Use the label as the input content |
| `emptyLabel` | Render the label without content |
| `valueAsInputContent` | Use the value as input content |
| `helpText` | Help text or closure |
| `checkable` | Add `checked` when selected |
| `selectable` | Add `selected` when selected |
| `useParentName` | Inherit the parent input's `name` attribute |
| `useInputNameAsValue` | Fall back to the input name when no value is resolved |

## Bags

All slot configuration lives in five bags. Use the `Default*` implementations
unless you need custom behavior.

### `DefaultComponentBag`

Component class and type per slot:

```php
use BackedEnum;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;

$bag = (new DefaultComponentBag)
    ->setWrapperType(ComponentEnum::FIELDSET)
    ->setLabelType(ComponentEnum::LEGEND)
    ->setInputType(ComponentEnum::TEXTAREA)
    ->setErrorType(ComponentEnum::PARAGRAPH)
    ->setHelpTextType(ComponentEnum::SMALL);

$bag->setInputComponent(
    fn (string|BackedEnum $type, ThemeManager $manager) => new MyInput($type, $manager),
);
```

### `DefaultAttributeBag`

Every slot accepts an array or a closure:

```php
use Juaniquillo\InputComponentAction\Bags\DefaultAttributeBag;

(new DefaultAttributeBag)
    ->setWrapperAttributes(['class' => 'field'])
    ->setLabelAttributes(['class' => 'field-label'])
    ->setInputAttributes(['placeholder' => 'Type here'])
    ->setErrorAttributes(['class' => 'field-error'])
    ->setHelpTextAttributes(['class' => 'field-help']);
```

Any slot also accepts a closure:

```php
(new DefaultAttributeBag)->setInputAttributes(fn ($input, $type) => [
    'name' => $input->getName(),
    'data-type' => $type instanceof BackedEnum ? $type->value : $type,
]);
```

### `DefaultThemeBag`

```php
use Juaniquillo\InputComponentAction\Bags\DefaultThemeBag;

(new DefaultThemeBag)
    ->setWrapperTheme(['forms' => 'one-column'])
    ->setLabelTheme(['color' => 'gray'])
    ->setInputTheme(['inputs' => 'default'])
    ->setErrorTheme(['color' => 'red'])
    ->setHelpTextTheme(['font' => 'sm']);
```

### `DefaultHookBag`

Hooks receive the built component and can replace or mutate it. Available hooks:
`input`, `wrapper`, `label`, `error`, `helpText`.

```php
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\InputComponentAction\Bags\DefaultHookBag;

(new DefaultHookBag)->setInputHook(
    function (BackendComponent $component): BackendComponent {
        return $component->setAttribute('data-hook', 'yes');
    },
);
```

### `DefaultDisableBag`

```php
use Juaniquillo\InputComponentAction\Bags\DefaultDisableBag;

(new DefaultDisableBag)
    ->setDisableWrapper()
    ->setDisableLabel()
    ->setDisableError()
    ->setDisableHelpText()
    ->setDisableInputValue()
    ->setDisableDefaultNameAttribute()
    ->setDisableDefaultIdAttribute()
    ->setDisableDefaultForAttribute();
```

## Groups

Set a group per input or as the action default:

```php
use Juaniquillo\InputComponentAction\Groups\LabelHelpTextInputErrorGroup;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;

$input->setRecipe(new InputComponentRecipe(
    inputGroup: new LabelHelpTextInputErrorGroup,
));

$action->setDefaultInputGroup(LabelHelpTextInputErrorGroup::class);
```

| Group | Slots |
| --- | --- |
| `DefaultInputGroup` | label, input, error |
| `InputErrorGroup` | input, error |
| `InputLabelErrorGroup` | input, label, error |
| `LabelHelpTextInputErrorGroup` | label, help text, input, error |
| `LabelContainerGroup` | label wrapping input, error |
| `SoleInputGroup` | input |
| `NoWrapInputGroup` | label, input, error (no wrapper element) |
| `NoWrapSoleInputGroup` | input (no wrapper element) |

### Custom groups

Extend `AbstractInputGroup` and declare the slot order:

```php
use Juaniquillo\InputComponentAction\Enums\Slot;
use Juaniquillo\InputComponentAction\Groups\AbstractInputGroup;

final class MyGroup extends AbstractInputGroup
{
    protected array $slots = [Slot::LABEL, Slot::INPUT, Slot::ERROR];
}
```

Available flags: `$wrap`, `$labelWrapsInput`, `$requiresLabel`.

For full control, implement `InputGroup` with `IsInputGroup` and the `Has*`
traits:

```php
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ContentComponent;
use Juaniquillo\InputComponentAction\Concerns\HasLabel;
use Juaniquillo\InputComponentAction\Concerns\HasWrapper;
use Juaniquillo\InputComponentAction\Concerns\IsInputGroup;
use Juaniquillo\InputComponentAction\Contracts\InputGroup;
use Juaniquillo\InputComponentAction\Utilities\Support;

final class LabelGroup implements InputGroup
{
    use HasLabel, HasWrapper, IsInputGroup;

    public function getGroup(): BackendComponent|ContentComponent
    {
        $wrapper = $this->getWrapperComponent() ?? Support::getCollectionWrapper();

        return $wrapper->setContents([
            $this->getLabelComponent(),
            $this->getInputComponent(),
        ]);
    }
}
```

Groups receive a `GroupContext` through `inject()`. Only override that method
if you need the raw context.

## Values and errors

### Value priority

1. The `values` array passed to the action.
2. The recipe `inputValue` (scalar or closure).
3. `$model->{name}`.

A recipe `valueBag` overrides the action value manager; the action manager
overrides `DefaultValueManager`.

### Error priority

1. The recipe `inputError`.
2. `errors[name]` (the last entry when it is an array).

Array values are passed through for multi-select/checkbox membership and are not
stringified into the input.

## Sub-elements

Sub-elements are resolved by `ChildInputResolver`. They honor `ignore()` and
`onlyFor()`; ineligible sub-elements are skipped, and if all are skipped the
input gets no contents.

### Select options

```php
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\OptionInput;
use Juaniquillo\CrudAssistant\Inputs\SelectInput;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;
use Juaniquillo\InputComponentAction\Recipes\SelectOptionComponentRecipe;

$select = new SelectInput('language', 'Language');
$select->setRecipe(new InputComponentRecipe(
    componentBag: (new DefaultComponentBag)->setInputType(ComponentEnum::SELECT),
));

$select->setSubElements(CrudAssistant::make([
    (new OptionInput('php', 'PHP'))->setRecipe(
        new SelectOptionComponentRecipe(inputValue: 'php'),
    ),
    (new OptionInput('js', 'JavaScript'))->setRecipe(
        new SelectOptionComponentRecipe(inputValue: 'js'),
    ),
]));
```

`SelectOptionComponentRecipe` extends the shared `ChoiceComponentRecipe` base and
configures the usual option defaults: `SoleInputGroup`, the `option` component
type, disabled wrapper/default name, `useParentValue`, `selectable`, and
`labelAsInputContent`. Passing `['language' => ['php', 'js']]` marks both options
selected.

### Radio and checkbox options

`RadioComponentRecipe` and `CheckboxComponentRecipe` configure a single choice
inside a group:

```php
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\CrudAssistant;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\CrudAssistant\Inputs\RadioGroupInput;
use Juaniquillo\InputComponentAction\Bags\DefaultComponentBag;
use Juaniquillo\InputComponentAction\Recipes\InputComponentRecipe;
use Juaniquillo\InputComponentAction\Recipes\RadioComponentRecipe;

$group = new RadioGroupInput('choice', 'Choice');
$group->setRecipe(new InputComponentRecipe(
    componentBag: (new DefaultComponentBag)->setInputType(ComponentEnum::DIV),
));

$group->setSubElements(CrudAssistant::make([
    (new DefaultInput('a', 'A'))->setRecipe(new RadioComponentRecipe),
    (new DefaultInput('b', 'B'))->setRecipe(new RadioComponentRecipe),
]));
```

`RadioComponentRecipe` inherits the parent `name`, uses the input name as the
value, and adds `checked` when it matches the parent value. Swap it for
`CheckboxComponentRecipe` for checkbox groups, or use it alone for a single
checkbox.

Both recipes only fill in defaults: a supplied component bag, group, or disable
bag is kept. That means you can still use a custom component (such as a switch)
or a label/error group. Use `setUseParentValue(false)` to opt out of parent
comparison.

All three recipes extend `ChoiceComponentRecipe`. The parent is passed through
the group context, so do not remove it when customizing sub-element resolution.

## Action configuration

```php
use Juaniquillo\BackendComponents\Themes\DefaultThemeManager;
use Juaniquillo\InputComponentAction\Groups\LabelHelpTextInputErrorGroup;
use Juaniquillo\InputComponentAction\InputComponentAction;

$action = (new InputComponentAction($values, $errors))
    ->setDefaultInputGroup(LabelHelpTextInputErrorGroup::class)
    ->setDefaultComponentBag($componentBag)
    ->setDefaultThemeBag($themeBag)
    ->setThemeManager(DefaultThemeManager::class)
    ->setModel($model)
    ->setValueManager($valueManager)
    ->setErrorManager($errorManager);
```

- `setThemeManager()` accepts a class string, a closure returning a
  `ThemeManager`, or `null` (uses `LocalThemeManager`).
- `setDefaultInputGroup()` accepts a class string, a closure returning an
  `InputGroup`, or `null` (uses `DefaultInputGroup`).
- `setModel()` is the final value fallback.
- `setValueManager()` / `setErrorManager()` override resolvers globally.

## Output and rendering

```php
$output = $crud->execute($action);

$component = $output->inputs->get('name'); // BackendComponent
$meta = $output->meta->all();
```

- `inputs` is keyed by input name.
- `meta` is free-form action metadata.
- Components implement `Htmlable`; render with `{{ $component }}`.

## Theming

Use `DefaultThemeManager` for the package's themes and `LocalThemeManager` for
the application's `resources/views/_themes` themes:

```php
use Juaniquillo\BackendComponents\Themes\LocalThemeManager;

$action->setThemeManager(LocalThemeManager::class);
```

Themes are applied per slot through `DefaultThemeBag`. A recipe theme bag wins
over the action default theme bag.

## Guardrails

- Do not build HTML strings manually. Build or configure backend components.
- Use the five bags instead of the removed per-slot contracts and traits.
- `InputComponentRecipe::make()` no longer exists; use `new InputComponentRecipe`.
- `Slot` cases are uppercase: `WRAPPER`, `LABEL`, `INPUT`, `ERROR`, `HELP_TEXT`.
- Put wrapper/label/input/error/help-text types on the component bag, not on
  the recipe. Recipe-level type setters were removed.
- Use `Support::resolveType($recipe, Slot::INPUT, $bag)` when you need the
  effective type.
- Keep the parent linkage when resolving sub-elements; `selected`/`checked`
  depend on it.
- Use `RadioComponentRecipe` / `CheckboxComponentRecipe` for radio and checkbox
  options instead of repeating the type, `checkable`, and layout defaults.
- Use `Default*Bag` classes unless a custom bag is genuinely required.

## Testing and QA

```bash
composer test       # Pest
composer analyse    # PHPStan level 8
composer format     # Pint
composer qa         # format -> analyse -> test
```

`laravel/pao` (dev) returns compact JSON when running inside an AI agent and
normal output otherwise.

## Upgrading

See `UPGRADE.md` in the package for the full pre-1.0 migration guide, including
the removed per-slot contracts, `InputComponentRecipe::make()`, the `GroupContext`
change, and the behavior changes around errors, arrays, and sub-elements.
