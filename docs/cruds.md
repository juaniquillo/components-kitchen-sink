# CRUD Schemas

[← Back to the README](../README.md) · [Component library](components.md)

Visit `/cruds` to see all available CRUD types. Each card links to its respective form, and every form submits back to `/cruds` with an `identifier` input.

Four different CRUD implementations demonstrate various theming approaches:

| Identifier | Name | Description |
|---|---|---|
| `unstyled` | Unstyled Crud | Basic form without special theming |
| `simple_crud` | Simple Themed Crud | Themed form with labels, inputs, and avatar |
| `flux` | Flux Crud | Flux-styled form built with the `flux-backend-components` package |
| `input_group` | Input Group WC | Form with label/input groups using a custom `LabelInputGroup` and the `form-group-web-component` view |

## Form patterns

Each CRUD follows a consistent pattern:

1. **Factories** define input fields (name, email, etc.)
2. **`CrudAssistant::make()`** creates an `InputCollection`
3. **`InputComponentAction`** processes values and errors
4. **`ComponentFactory`** constructs the final HTML component
5. **Form submits to `/cruds`** with `identifier` input
