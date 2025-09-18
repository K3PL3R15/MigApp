<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view for MigApp.
     */
    public function create(): View
    {
        return view('auth.register', [
            'roles' => User::ROLES
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', 'in:owner,manager,employee'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Si el rol es manager o employee, se requiere el código de sucursal
        if (in_array($request->role, ['manager', 'employee'])) {
            $rules['branch_code'] = [
                'required', 
                'string', 
                'exists:branches,unique_code',
                function ($attribute, $value, $fail) {
                    $branch = Branch::where('unique_code', $value)->first();
                    if (!$branch) {
                        $fail('El código de sucursal no existe.');
                    }
                }
            ];
        }

        $validated = $request->validate($rules, [
            'role.in' => 'El rol seleccionado no es válido.',
            'branch_code.required' => 'El código de sucursal es obligatorio para managers y empleados.',
            'branch_code.exists' => 'El código de sucursal no existe o no es válido.',
        ]);

        $branch_id = null;
        if (in_array($request->role, ['manager', 'employee'])) {
            $branch = Branch::where('unique_code', $request->branch_code)->first();
            $branch_id = $branch ? $branch->id_branch : null;
            
            if (!$branch_id) {
                return redirect()->back()
                    ->withErrors(['branch_code' => 'Código de sucursal inválido.'])
                    ->withInput();
            }
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'id_branch' => $branch_id,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));
            Auth::login($user);

            // Si es dueño y no tiene sucursales, redirige a crear sucursal obligatoriamente
            if ($user->role === 'owner' && $user->branches()->count() === 0) {
                session(['just_registered' => true]);
                return redirect()->route('branches.create')
                    ->with('info', 'Bienvenido a MigApp. Para continuar, crea tu primera sucursal.');
            }

            // Mensaje de bienvenida personalizado según el rol
            $welcomeMessage = match ($user->role) {
                'owner' => '¡Bienvenido a MigApp! Tu sucursal ha sido configurada correctamente.',
                'manager' => '¡Bienvenido a MigApp! Te has registrado como gerente en ' . ($branch->name ?? 'la sucursal') . '.',
                'employee' => '¡Bienvenido a MigApp! Te has registrado como empleado en ' . ($branch->name ?? 'la sucursal') . '.',
                default => '¡Bienvenido a MigApp!'
            };

            return redirect(route('dashboard'))->with('success', $welcomeMessage);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => 'Error al crear la cuenta. Intenta nuevamente.'])
                ->withInput();
        }
    }
}
