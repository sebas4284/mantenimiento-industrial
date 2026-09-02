<div>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Equipo de trabajo</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Técnicos y supervisores de mantenimiento</p>
            </div>

            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre..."
                class="w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($members as $member)
                @php
                    $isBusy = ($member->active_assigned_count + $member->active_support_count) > 0;
                @endphp
                <div wire:key="member-{{ $member->id }}" class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5 flex flex-col gap-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                <a href="{{ route('team.show', $member) }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $member->name }}</a>
                            </h3>
                            <x-badge color="zinc">{{ $member->role->label() }}</x-badge>
                        </div>
                        <x-badge :color="$isBusy ? 'amber' : 'green'">{{ $isBusy ? 'Ocupado' : 'Disponible' }}</x-badge>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-0.5">
                        <p>{{ $member->email }}</p>
                        @if ($member->plant)
                            <p>{{ $member->plant->name }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-sm text-gray-500 dark:text-gray-400 py-12">No hay colaboradores registrados todavía.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $members->links() }}</div>
    </div>
</div>
