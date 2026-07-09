<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold text-slate-900">Site settings</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="rounded-xl border border-red-900/10 bg-white/85 p-6">
            <h2 class="font-display text-xl font-semibold text-slate-900">Current logo</h2>
            <div class="mt-4 flex items-center gap-4 rounded-lg border border-slate-200 bg-red-50/50 p-4">
                <img src="{{ $logoPreview }}" alt="Current logo" class="h-20 w-auto object-contain">
                <div class="text-sm text-slate-600 break-all">
                    <p class="font-medium text-slate-800">Preview</p>
                    <p class="mt-1">{{ $logoPreview }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 rounded-xl border border-red-900/10 bg-white/85 p-6">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="site_name" value="Site name" />
                <x-text-input id="site_name" name="site_name" class="mt-1 block w-full" :value="old('site_name', $siteName)" required />
                <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="logo_url" value="Logo URL or path" />
                <x-text-input id="logo_url" name="logo_url" class="mt-1 block w-full" :value="old('logo_url', $logoUrl)" placeholder="images/logo-new.png or https://..." />
                <p class="mt-1 text-xs text-slate-500">Use a full URL, or a public path like <code>images/logo-new.png</code>.</p>
                <x-input-error :messages="$errors->get('logo_url')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="logo_file" value="Or upload a logo file" />
                <input id="logo_file" name="logo_file" type="file" accept="image/*"
                       class="mt-1 block w-full text-sm text-slate-600 file:me-3 file:rounded-md file:border-0 file:bg-red-800 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-red-700" />
                <p class="mt-1 text-xs text-slate-500">PNG/JPG/WebP up to 2MB. Upload overrides the URL field.</p>
                <x-input-error :messages="$errors->get('logo_file')" class="mt-2" />
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button>Save settings</x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.settings.reset-logo') }}" onsubmit="return confirm('Reset logo to the default Mwasalat image?')">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-md border border-red-800/30 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">
                Reset to default logo
            </button>
        </form>
    </div>
</x-app-layout>
