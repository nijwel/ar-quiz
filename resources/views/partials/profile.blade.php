@php
    $user = auth()->user();
    $initialTab = $errors->hasAny(['current_password', 'password']) ? 'password' : ($errors->any() ? 'edit' : 'details');
    $details = array_filter([
        ['Full Name', $user->name, 'icon-[tabler--user]'],
        ['Email Address', $user->email, 'icon-[tabler--mail]'],
        $user->student_id ? ['Student ID', '#' . $user->student_id, 'icon-[tabler--id-badge-2]'] : null,
        ['Joined', $user->created_at->format('d M, Y'), 'icon-[tabler--calendar]'],
    ]);
@endphp

<div x-data="{ tab: '{{ $initialTab }}' }" class="mx-auto max-w-4xl">
    {{-- Identity card --}}
    <section class="relative mb-6 animate-rise overflow-hidden rounded-[2rem] glass-panel text-white">
        <div class="absolute inset-0 bg-grid [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
        <div class="absolute -top-20 right-10 size-72 rounded-full bg-brand-600/50 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-10 size-56 rounded-full bg-volt-400/15 blur-3xl"></div>

        <div class="relative flex flex-col items-center gap-5 p-8 text-center sm:flex-row sm:text-left">
            <div class="relative">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=ff2a46&color=fff&size=160&bold=true"
                    alt="" class="size-24 rounded-3xl ring-4 ring-white/10">
                <span class="absolute -right-1 -bottom-1 grid size-7 place-items-center rounded-full bg-emerald-500 ring-4 ring-ink-950">
                    <span class="icon-[tabler--check] size-4"></span>
                </span>
            </div>
            <div>
                <h1 class="font-display text-3xl font-bold">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-ink-300">{{ $user->email }}</p>
                <span class="badge mt-3 bg-volt-400 text-snow">{{ $roleLabel }}</span>
            </div>
        </div>

        <nav class="relative flex gap-1 border-t border-white/10 px-4 sm:px-8">
            @foreach (['details' => ['Details', 'icon-[tabler--id]'], 'edit' => ['Update Info', 'icon-[tabler--user-edit]'], 'password' => ['Password', 'icon-[tabler--lock]']] as $key => [$label, $icon])
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'text-white after:bg-volt-400' : 'text-ink-400 hover:text-white after:bg-transparent'"
                    class="relative flex items-center gap-2 px-4 py-4 text-sm font-semibold transition after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full">
                    <span class="{{ $icon }} size-4"></span> {{ $label }}
                </button>
            @endforeach
        </nav>
    </section>

    {{-- Details --}}
    <div x-show="tab === 'details'" x-transition.opacity class="grid gap-4 sm:grid-cols-2">
        @foreach ($details as [$label, $value, $icon])
            <div class="card flex items-center gap-4 p-5">
                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-brand-500/10 text-brand-400">
                    <span class="{{ $icon }} size-5"></span>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-bold tracking-wider text-ink-400 uppercase">{{ $label }}</p>
                    <p class="truncate font-semibold text-white">{{ $value }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Edit --}}
    <div x-cloak x-show="tab === 'edit'" x-transition.opacity class="card p-6 sm:p-8">
        <h2 class="mb-6 font-display text-xl font-bold text-white">Update Profile Details</h2>
        <form action="{{ $profileRoute }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="input @error('name') input-error @enderror" required>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="label" for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="input @error('email') input-error @enderror {{ $emailEditable ? '' : 'cursor-not-allowed opacity-60' }}"
                        {{ $emailEditable ? '' : 'readonly' }} required>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--device-floppy] size-4"></span> Save Changes
            </button>
        </form>
    </div>

    {{-- Password --}}
    <div x-cloak x-show="tab === 'password'" x-transition.opacity class="card p-6 sm:p-8">
        <h2 class="mb-6 font-display text-xl font-bold text-white">Change Password</h2>
        <form action="{{ $passwordRoute }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="label" for="current_password">Current Password</label>
                <input id="current_password" type="password" name="current_password"
                    class="input @error('current_password') input-error @enderror" required>
                @error('current_password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">New Password</label>
                    <input id="password" type="password" name="password"
                        class="input @error('password') input-error @enderror" required>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="label" for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--key] size-4"></span> Update Password
            </button>
        </form>
    </div>
</div>
