<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredVendorController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.vendor.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class .',email'],
            'owner_password' => ['required', 'confirmed', Rules\Password::defaults()],
            'owner_phone' => ['required', 'string', 'max:255'],
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:3072'],
        ]);
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'password' => Hash::make($request->owner_password),
                'role' => 'vendor',
                'is_active' => true,
                'is_verified' => false,
            ]);

            $user->assignRole('vendor');

            $vendor = Vendor::create([
                'slug' => Str::slug($request->name['en']),
                'owner_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'image' => $request->image,
                'commission_percentage' => setting('commission_percentage'),
            ]);
            DB::commit();

            $user->sendEmailVerificationNotification();
            event(new Registered($user));

            Auth::login($user);
            $request->session()->put('email', $user->email);
            // return to verification page
            return redirect(route('auth.verify-code'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }
}
