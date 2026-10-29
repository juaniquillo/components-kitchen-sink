<x-layouts.default page-title="Component Builder">

    <div class="mx-auto max-w-xl">

        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold">Component Builder</h1>

            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Compose a component by dragging it together, then copy the generated PHP.
            </p>
        </div>

        @if (session('builder-notice'))
            <p class="mb-4 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-900 dark:bg-amber-500/10 dark:text-amber-300" role="alert">
                {{ session('builder-notice') }}
            </p>
        @endif

        <div class="space-y-4">

            <section class="rounded-lg bg-white p-4 shadow-sm dark:bg-slate-800">
                <h2 class="text-sm font-semibold">Start a workspace</h2>

                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    A workspace is a canvas you can come back to. Its address is the
                    whole of the way in, so keep the link: anyone holding it can open
                    the workspace and work on it.
                </p>

                {{--
                    A workspace is made by a plain form and not by a script: the
                    address it lands on is a page of its own, and the browser
                    follows a redirect like it follows any other.
                --}}
                <form method="POST" action="{{ route('builder.store') }}" class="mt-3">
                    @csrf

                    <button
                        type="submit"
                        class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-500"
                    >
                        New workspace
                    </button>
                </form>
            </section>

            <section class="rounded-lg bg-white p-4 shadow-sm dark:bg-slate-800">
                <h2 class="text-sm font-semibold">Open a workspace</h2>

                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Paste a link someone shared with you, or the id at the end of it.
                </p>

                <form method="POST" action="{{ route('builder.open') }}" class="mt-3 flex flex-wrap items-end gap-2">
                    @csrf

                    <label class="min-w-0 flex-1">
                        <span class="mb-1 block text-[11px] font-medium text-slate-500">Link or id</span>

                        <input
                            type="text"
                            name="link"
                            value="{{ old('link') }}"
                            placeholder="https://example.test/builder/01J..."
                            class="w-full rounded border border-slate-200 px-2 py-1 text-sm dark:border-slate-600 dark:bg-slate-900"
                        />
                    </label>

                    <button
                        type="submit"
                        class="rounded border border-slate-200 px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600"
                    >
                        Open
                    </button>
                </form>
            </section>

        </div>
    </div>

</x-layouts.default>
