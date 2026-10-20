<section class="rounded-lg bg-slate-900 p-4 shadow-sm" x-data="{
    copied: false,
    failed: false,
    async copy(text) {
        let copied = false;

        try {
            // The clipboard api is only there in a secure context, so it is
            // asked for before it is used, and the selection is used when it
            // is not there or when it refuses.
            copied = navigator.clipboard
                ? await navigator.clipboard.writeText(text).then(() => true).catch(() => this.copyWithSelection(text))
                : this.copyWithSelection(text);
        } catch (e) {
            console.warn('Could not copy to clipboard', e);
        }

        this.copied = copied;
        this.failed = ! copied;

        setTimeout(() => { this.copied = false; this.failed = false; }, 1500);
    },
    copyWithSelection(text) {
        const area = document.createElement('textarea');
        const focused = document.activeElement;

        area.value = text;
        area.setAttribute('readonly', '');
        area.style.cssText = 'position: fixed; top: 0; left: 0; opacity: 0;';
        document.body.appendChild(area);
        area.select();
        area.setSelectionRange(0, area.value.length);

        const copied = document.execCommand('copy');

        area.remove();
        focused?.focus();

        return copied;
    }
}">
    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-xs font-semibold tracking-wide text-slate-400 uppercase">Generated code</h2>

        <button
            type="button"
            x-on:click="copy($refs.code.textContent)"
            class="rounded bg-indigo-600 px-3 py-1 text-xs text-white transition hover:bg-indigo-500"
        >
            <span x-text="copied ? 'Copied' : (failed ? 'Copy failed' : 'Copy')" aria-live="polite">Copy</span>
        </button>
    </div>

    <pre x-ref="code" class="max-h-80 overflow-auto text-xs leading-relaxed text-slate-100"><code>{{ $this->code }}</code></pre>
</section>
