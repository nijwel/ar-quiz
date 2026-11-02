@php $category = $category ?? null; @endphp

<form action="{{ $action }}" method="POST" class="card mx-auto max-w-2xl space-y-5 p-6 sm:p-8"
    x-data="{ name: @js(old('name', $category?->name ?? '')), slug: @js(old('slug', $category?->slug ?? '')), slugEdited: {{ $category ? 'true' : 'false' }} }">
    @csrf
    @if ($category)
        @method('PUT')
    @endif

    <div>
        <label class="label" for="name">Name</label>
        <input id="name" name="name" x-model="name" required maxlength="255" placeholder="e.g. Python"
            @input="if (!slugEdited) slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')"
            class="input @error('name') input-error @enderror">
        @error('name') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="slug">Slug <span class="font-normal text-ink-400">(URL-এ ব্যবহার হয়)</span></label>
        <input id="slug" name="slug" x-model="slug" @input="slugEdited = true" maxlength="255" placeholder="python"
            class="input font-mono text-sm @error('slug') input-error @enderror">
        @error('slug') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="parent_id">Group <span class="font-normal text-ink-400">(optional)</span></label>
        @if ($category?->children_count)
            <p class="rounded-xl bg-white/5 px-4 py-3 text-sm text-ink-300 ring-1 ring-white/10">
                এটি নিজেই একটি গ্রুপ ({{ $category->children_count }}টি সাব-ক্যাটেগরি), তাই অন্য গ্রুপের ভেতরে রাখা যাবে না।
            </p>
        @else
            <select id="parent_id" name="parent_id" class="input @error('parent_id') input-error @enderror">
                <option value="">— No group (top-level subject) —</option>
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}" @selected(old('parent_id', $category?->parent_id) == $group->id)>{{ $group->name }}</option>
                @endforeach
            </select>
            <p class="mt-1.5 text-xs text-ink-400">
                যেমন PHP → Programming। স্টুডেন্ট পুরো গ্রুপ বেছে নিলে এর সব সাব-ক্যাটেগরির পরীক্ষা দিতে পারবে।
            </p>
            @error('parent_id') <p class="field-error">{{ $message }}</p> @enderror
        @endif
    </div>

    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
        <span>
            <span class="block font-semibold text-white">Active</span>
            <span class="text-xs text-ink-400">বন্ধ থাকলে স্টুডেন্টরা এই ক্যাটেগরি ও এর পরীক্ষা দেখবে না।</span>
        </span>
        <input type="hidden" name="status" value="0">
        <input type="checkbox" name="status" value="1" class="size-5 accent-brand-600" @checked(old('status', $category?->status ?? true))>
    </label>

    <div class="flex justify-end gap-2 pt-2">
        <a href="{{ route('category.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="icon-[tabler--device-floppy] size-4"></span> {{ $submitLabel }}
        </button>
    </div>
</form>
