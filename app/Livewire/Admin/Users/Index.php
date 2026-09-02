<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserRole;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;

    public ?User $editing = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'tecnico';

    public ?int $plant_id = null;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(User $user): void
    {
        $this->editing = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role->value;
        $this->plant_id = $user->plant_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorize($this->editing ? 'update' : 'create', $this->editing ?? User::class);

        $seesAllPlants = UserRole::tryFrom($this->role)?->seesAllPlants() ?? false;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editing?->id)],
            'password' => [$this->editing ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', new Enum(UserRole::class)],
            'plant_id' => $seesAllPlants ? ['nullable'] : ['required', 'exists:plants,id'],
        ]);

        $plantId = UserRole::from($validated['role'])->seesAllPlants() ? null : $validated['plant_id'];

        $user = $this->editing ?? new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->plant_id = $plantId;

        if ($validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(User $user): void
    {
        $this->authorize('delete', $user);

        $user->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'tecnico';
        $this->plant_id = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.users.index', [
            'users' => User::with('plant')->orderBy('name')->paginate(15),
            'plants' => Plant::orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }
}
