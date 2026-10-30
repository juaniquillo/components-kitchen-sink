# TODO

## Cruds (`/cruds`)

- [ ] **Input Group WC.** Fix `resources\js\form-group.js` Web Component. Right now 
      it's buggy if the user submits the form without clicking any of the inputs

## Builder (`/builder`)

### Theme system

- [ ] **Rename a group or a variant.** `action / primary` can only become
      `cta / primary` by recreating every variant and re-applying it to each node.
      `TreeMutator` could rewrite the reference in the tree in the same transaction,
      which keeps the change contained.
- [ ] **Delete a group, or the unused themes, in one go.** Cleanup is one variant at
      a time, and a variant that is in use cannot be deleted. A "delete this group"
      action (offered when no variant in the group is used) and a "show only unused"
      filter would pair with the `used by N` counts that are already there.

### Deferred

- [ ] **Keybindings.** Ctrl+D and the rest of the full set; its own session.
- [ ] **Conflict detection.** A stale tab still last-write-wins silently. An
      `updated_at` on the workspace envelope plus a mismatch check would turn a silent
      overwrite into a notice — a design decision first, not a fix.
- [ ] **Theme import.** The `.zip` export is one-way. A JSON import (paste or file)
      would make the palette portable between workspaces as well as into projects.

## App-wide

- [ ] **Navigation contrast.** The shared nav's `text-blue-500` on the slate-700 body
      is 2.81:1, the only axe violation left on the builder page. `dark:text-blue-300`
      or a darker light-mode base.

## Design System

To be discussed. 