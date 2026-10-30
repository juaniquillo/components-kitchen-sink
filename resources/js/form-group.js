const formGroupStyles = new CSSStyleSheet();

formGroupStyles.replaceSync(`

  .error-message {
    color: var(--error-message-color, red);
  }

  .error-message:empty {
    display: none;
  }

`);

class FormGroup extends HTMLElement {
  #inputs = [];
  #abortController = null;

  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.adoptedStyleSheets = [formGroupStyles];
    this.shadowRoot.innerHTML = `
      <slot></slot>
      <span class="error-message"
            aria-live="polite"
            part="error-message">
      </span>
    `

    // bind event handlers since were applying them
    // to an external element and need the `this`
    // context to refer to this class
    this.handleInvalid = this.handleInvalid.bind(this);
    this.handleInput = this.handleInput.bind(this);
    this.handleBlur = this.handleBlur.bind(this);
  }

  handleInvalid(event) {
    // turn off the browser validation popup and show
    // the message below the field instead. `invalid` fires
    // on every invalid control when a submit is blocked,
    // whatever triggered the submit, so this is also what
    // validates a form nobody has touched yet.
    event.preventDefault();
    this.update();
  }

  handleInput() {
    // hide the error message while the user is typing
    this.setMessage('');
    this.setAriaInvalid(false);
  }

  handleBlur() {
    // if an input is NOT valid, show an error message
    this.update();
  }

  connectedCallback() {
    // a group holds every control of one field, so all of them
    // are wired: a radio group, for example, is only valid once
    // one of its buttons is checked.
    this.#inputs = [...this.querySelectorAll("input, textarea, select")];

    // connecting again replaces the listeners instead of doubling them
    this.#abortController?.abort();
    this.#abortController = new AbortController();
    const { signal } = this.#abortController;

    for (const input of this.#inputs) {
      input.addEventListener('invalid', this.handleInvalid, { signal });
      input.addEventListener('input', this.handleInput, { signal });
      input.addEventListener('blur', this.handleBlur, { signal });
    }
  }

  disconnectedCallback() {
    // clean up all event listeners
    this.#abortController?.abort();
    this.#abortController = null;
  }

  update() {
    const invalid = this.#inputs.find((input) => !input.validity.valid);

    if (!invalid) {
      this.setMessage('');
      this.setAriaInvalid(false);

      return;
    }

    const key = this.#getFirstInvalid(invalid.validity);

    this.setMessage((key && this.#customErrorMessage[key]) || 'This field is invalid');
    this.setAriaInvalid(true);
  }

  setMessage(text) {
    const message = this.#errorMessage;

    if (message.textContent !== text) {
      message.textContent = text;
    }
  }

  setAriaInvalid(invalid) {
    for (const input of this.#inputs) {
      if (invalid) {
        input.setAttribute('aria-invalid', 'true');
      } else {
        input.removeAttribute('aria-invalid');
      }
    }
  }

  get #errorMessage() {
    return this.shadowRoot.querySelector('.error-message');
  }

  #getFirstInvalid(validityState) {
    for (const key in validityState) {
      if (validityState[key] === true) {
        return key;
      }
    }

    return null;
  }

  get #customErrorMessage() {
    return {
      valueMissing: this.getAttribute('value-missing-message') || 'This field is required',
      tooLong: this.getAttribute('too-long-message') || 'This field is too long',
      tooShort: this.getAttribute('too-short-message') || 'This field is too short',
      rangeOverflow: this.getAttribute('range-overflow-message') || 'This field has a number that is too big',
      rangeUnderflow: this.getAttribute('range-underflow-message') || 'This field has a number that is too small',
      typeMismatch: this.getAttribute('type-mismatch-message') || 'This field is the wrong type',
      patternMismatch: this.getAttribute('pattern-mismatch-message') || 'This fields value does not match the pattern',
    }
  }
}



customElements.define("form-group", FormGroup);
