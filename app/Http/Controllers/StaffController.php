<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $roleId = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'online') {
                $query->where('last_seen', '>', now()->subMinutes(5));
            } elseif ($status === 'offline') {
                $query->where(function ($q) {
                    $q->where('last_seen', '<=', now()->subMinutes(5))
                        ->orWhereNull('last_seen');
                });
            } elseif ($status === 'banned') {
                $query->where('status', 0);
            } elseif ($status === 'active') {
                $query->where('status', 1);
            }
        }

        $sort = $request->input('sort', 'desc');
        $query->orderByRaw('id = ? DESC', [Auth::id()])
            ->orderBy('created_at', $sort);

        $perPage = GeneralSetting::first()->records_per_page ?? 10;
        $staffs = $query->paginate($perPage)->withQueryString();
        $roles = Role::all();

        return view('staff.index', compact('staffs', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 1,
        ]);

        $user->roles()->attach($request->role_id);

        return redirect()->back()->with('success', 'Staff added successfully.');
    }

    public function update(Request $request, User $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$staff->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
        ]);

        $staff->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $staff->password,
        ]);

        $staff->roles()->sync([$request->role_id]);

        return redirect()->back()->with('success', 'Staff updated successfully.');
    }

    public function toggleStatus(Request $request, User $staff)
    {
        $staff->status = ! $staff->status;
        $staff->save();

        if ($request->has('redirect_to_login') && $staff->status == 1) {
            return $this->loginAs($staff);
        }

        $status = $staff->status ? 'Active' : 'Banned';

        return redirect()->back()->with('success', "Staff status changed to $status.");
    }

    public function loginAs(User $staff)
    {
        // Save the current user ID to the session so we can return to it later
        session(['impersonated_by' => Auth::id()]);

        Auth::login($staff);

        return redirect()->route('dashboard')->with('success', "You are now logged in as {$staff->name}.");
    }

    public function stopImpersonating()
    {
        if (session()->has('impersonated_by')) {
            $admin = User::find(session('impersonated_by'));
            Auth::login($admin);
            session()->forget('impersonated_by');

            return redirect()->route('staff.index')->with('success', 'You have returned to your account.');
        }

        return redirect()->route('dashboard');
    }

    public function getStatuses()
    {
        $users = User::all(['id', 'last_seen', 'status']);
        $statuses = $users->mapWithKeys(function ($user) {
            $status = 'offline';
            if ($user->status == 0) {
                $status = 'banned';
            } elseif ($user->id === Auth::id()) {
                $status = 'you';
            } elseif ($user->isOnline()) {
                $status = 'online';
            }

            return [$user->id => $status];
        });

        return response()->json($statuses);
    }
}
