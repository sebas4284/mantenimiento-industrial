<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-users-three text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Equipo de trabajo</h1>
    </div>
</x-slot>

<div>
    <div class="flex items-center justify-between gap-4 mb-4">
        <p class="text-sm text-neutral-400 m-0">Técnicos y supervisores de mantenimiento</p>
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre..." class="input max-w-sm">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($members as $member)
            @php
                $isBusy = ($member->active_assigned_count + $member->active_support_count) > 0;
            @endphp
            <div wire:key="member-{{ $member->id }}" class="card elev-sm p-5 gap-2">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-medium text-ink m-0">
                            <a href="{{ route('team.show', $member) }}" wire:navigate class="text-ink hover:text-accent-300">{{ $member->name }}</a>
                        </h3>
                        <span class="tag tag-neutral">{{ $member->role->label() }}</span>
                    </div>
                    <span class="tag {{ $isBusy ? 'tag-accent' : 'tag-neutral' }}">{{ $isBusy ? 'Ocupado' : 'Disponible' }}</span>
                </div>

                <div class="text-sm text-muted">
                    <p class="m-0">{{ $member->email }}</p>
                    @if ($member->plant)
                        <p class="m-0">{{ $member->plant->name }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-muted py-12">No hay colaboradores registrados todavía.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $members->links() }}</div>
</div>
