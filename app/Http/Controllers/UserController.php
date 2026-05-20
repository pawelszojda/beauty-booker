<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->select(['id', 'name', 'email', 'role', 'email_verified_at', 'created_at'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Users/Create', [
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyCustomerAccess();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($this->roles())],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.index');
    }

    public function edit(User $user): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Users/Edit', [
            'managedUser' => $user->only(['id', 'name', 'email', 'role']),
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->denyCustomerAccess();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($this->roles())],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->denyCustomerAccess();

        if (auth()->id() === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();

        return redirect()->route('users.index');
    }

    private function denyCustomerAccess(): void
    {
        abort_if(auth()->user()?->isCustomer(), 403);
    }

    /**
     * @return array<int, string>
     */
    private function roles(): array
    {
        return ['administrator', 'stylist', 'customer'];
    }
}
