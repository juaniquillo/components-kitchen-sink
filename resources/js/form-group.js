const formGroupStyles = new CSSStyleSheet();

formGroupStyles.replaceSync(`
  .error-message {
    display: block;
    color: var(--error-message-color, red);
  }
`);

// The validity states in the order their messages take precedence: a field
// that is empty and too short is missing, not short.
const VALIDITY_ORDER = [
  'valueMissing',
  'typeMismatch',
  'patternMismatch',
  'tooShort',
  'tooLong',
  'rangeUnderflow',
  'rangeOverflow',
  'stepMismatch',
  'badInput',
  'customError',
];

class FormGroup extends HTMLElement {
  #inputs = [];
  #form = null;
  #abortController = null;

  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.adoptedStyleSheets = [formGroupStyles];
    // The message stays in the tree while it is empty: an empty block takes
    // no room, and a region that leaves the tree is not announced when it
    // comes back.
    this.shadowRoot.innerHTML = `
      <slot></slot>
      <span class="error-message" aria-live="polite" part="error-message"></span>
    `;

    // bind event handlers since we're applying them
    // to external elements and need the `this`
    // context to refer to this class
    this.handleInvalid = this.handleInvalid.bind(this);
    this.handleInput = this.handleInput.bind(this);
    this.handleFocusOut = this.handleFocusOut.bind(this);
    this.handleReset = this.handleReset.bind(this);
  }

  handleInvalid(event) {
    // turn off the browser validation popup and show the message below the
    // field instead. `invalid` fires on every invalid control when a submit
    // is blocked, whatever triggered the submit, so this is also what
    // validates a form nobody has touched yet.
    event.preventDefault();
    this.update();

    // Without the popup the browser no longer puts the caret on the first
    // field to fix, so it is done here instead. The microtask waits out the
    // submit, so the focus lands after the browser has finished blocking it.
    queueMicrotask(() => {
      this.#form?.querySelector(':invalid')?.focus();
    });
  }

  handleInput() {
    // hide the error message while the user is typing; the invalid marker
    // follows each control's own validity instead.
    this.setMessage('');
    this.syncAriaInvalidity();
  }

  handleFocusOut(event) {
    // Moving between the controls of one group blurs one before the next is
    // checked, and the group is momentarily invalid. The message waits until
    // the focus truly leaves the group.
    if (event.relatedTarget instanceof Node && this.contains(event.relatedTarget)) {
      return;
    }

    this.update();
  }

  handleReset() {
    // A reset restores the values but leaves anything the script wrote, so
    // the message and the markers go back to how the page loaded: none on
    // screen. The event fires before the values change, so this waits a tick.
    queueMicrotask(() => {
      this.setMessage('');

      for (const input of this.#inputs) {
        input.removeAttribute('aria-invalid');
      }
    });
  }

  connectedCallback() {
    // A group holds every control of one field, so all of them are wired: a
    // radio group, for example, is only valid once one of its buttons is
    // checked.
    this.#inputs = [...this.querySelectorAll("input, textarea, select")];
    this.#form = this.closest('form');

    // connecting again replaces the listeners instead of doubling them
    this.#abortController?.abort();
    this.#abortController = new AbortController();
    const { signal } = this.#abortController;

    for (const input of this.#inputs) {
      input.addEventListener('invalid', this.handleInvalid, { signal });
      input.addEventListener('input', this.handleInput, { signal });
      input.addEventListener('focusout', this.handleFocusOut, { signal });
    }

    this.#form?.addEventListener('reset', this.handleReset, { signal });
  }

  disconnectedCallback() {
    // clean up all event listeners
    this.#abortController?.abort();
    this.#abortController = null;
    this.#form = null;
  }

  update() {
    const invalid = this.#inputs.find((input) => !input.validity.valid);

    this.setMessage(invalid ? this.#messageFor(invalid) : '');
    this.syncAriaInvalidity();
  }

  setMessage(text) {
    const message = this.#errorMessage;

    if (message.textContent !== text) {
      message.textContent = text;
    }
  }

  syncAriaInvalidity() {
    for (const input of this.#inputs) {
      if (input.validity.valid) {
        input.removeAttribute('aria-invalid');
      } else {
        input.setAttribute('aria-invalid', 'true');
      }
    }
  }

  get #errorMessage() {
    return this.shadowRoot.querySelector('.error-message');
  }

  #messageFor(input) {
    for (const key of VALIDITY_ORDER) {
      if (input.validity[key] === true) {
        return this.#customErrorMessage[key] || input.validationMessage;
      }
    }

    return input.validationMessage || 'This field is invalid';
  }

  get #customErrorMessage() {
    return {
      valueMissing: this.getAttribute('value-missing-message') || 'This field is required',
      tooLong: this.getAttribute('too-long-message') || 'This field is too long',
      tooShort: this.getAttribute('too-short-message') || 'This field is too short',
      rangeOverflow: this.getAttribute('range-overflow-message') || 'This field has a number that is too big',
      rangeUnderflow: this.getAttribute('range-underflow-message') || 'This field has a number that is too small',
      typeMismatch: this.getAttribute('type-mismatch-message') || 'This field is the wrong type',
      patternMismatch: this.getAttribute('pattern-mismatch-message') || "This field's value does not match the pattern",
    }
  }
}

if (!customElements.get('form-group')) {
  customElements.define('form-group', FormGroup);
}
