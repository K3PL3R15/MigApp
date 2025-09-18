<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:owner,manager', only: ['index', 'edit', 'update', 'destroy']),
        ];
    }

    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($authUser->role === 'owner') {
            // IDs de sucursales creadas por el dueño autenticado
            $branchIds = Branch::where('id_user', $authUser->id)->pluck('id_branch');

            // Usuarios de esas sucursales, excluyendo otros owners
            $users = User::with('branch')
                ->where(function ($q) use ($branchIds, $authUser) {
                    $q->whereIn('id_branch', $branchIds)
                    ->orWhere('id', $authUser->id);
                })
                ->where('role', '!=', 'owner') // Excluir otros owners
                ->orWhere('id', $authUser->id) // Incluir al owner actual
                ->get();

            $branches = Branch::where('id_user', $authUser->id)->get();
            $roles = User::ROLES;

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'users' => $users,
                    'can_create' => true
                ]);
            }

            return view('users.index', compact('users', 'branches', 'roles'));
        }

        if ($authUser->role === 'manager') {
            // Solo empleados de su sucursal
            $users = User::where('id_branch', $authUser->id_branch)
                ->where('role', 'employee')
                ->get();

            $roles = User::ROLES;
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'users' => $users,
                    'can_create' => false
                ]);
            }

            return view('users.index', compact('users', 'roles'));
        }

        if ($authUser->role === 'employee') {
            // Solo puede verse a sí mismo
            $users = User::where('id', $authUser->id)->get();
            $roles = User::ROLES;
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'users' => $users,
                    'can_create' => false
                ]);
            }

            return view('users.index', compact('users', 'roles'));
        }

        abort(403, 'Acceso no autorizado');
    }

    public function edit(Request $request, User $user)
    {
        $authUser = Auth::user();

        if ($authUser->role === 'owner') {
            // Verificar que el usuario pertenece a sus sucursales
            $userBranches = Branch::where('id_user', $authUser->id)->pluck('id_branch');
            if ($user->id !== $authUser->id && !$userBranches->contains($user->id_branch)) {
                abort(403, 'No tiene permisos para editar este usuario.');
            }
            
            $branches = Branch::where('id_user', $authUser->id)->get();
            $roles = ['manager' => 'Gerente', 'employee' => 'Empleado']; // Owner no puede cambiar roles a owner
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('users.partials.edit-form', compact('user', 'branches', 'roles'))->render()
                ]);
            }
            
            return view('users.edit', compact('user', 'branches', 'roles'));
        }

        if ($authUser->role === 'manager' && $authUser->id_branch === $user->id_branch) {
            $roles = ['employee' => 'Empleado']; // Manager solo puede editar empleados
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('users.partials.edit-form', compact('user', 'roles'))->render()
                ]);
            }
            
            return view('users.edit', compact('user', 'roles'));
        }

        abort(403, 'Acceso no autorizado');
    }

    public function update(Request $request, User $user)
    {
        $authUser = Auth::user();

        if ($authUser->role === 'owner') {
            // Verificar ownership
            $userBranches = Branch::where('id_user', $authUser->id)->pluck('id_branch');
            if ($user->id !== $authUser->id && !$userBranches->contains($user->id_branch)) {
                abort(403, 'No tiene permisos para editar este usuario.');
            }
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
                'role' => ['required', Rule::in(['manager','employee'])],
                'id_branch' => 'nullable|exists:branches,id_branch',
            ]);
            
        } elseif ($authUser->role === 'manager' && $authUser->id_branch === $user->id_branch) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
            ]);
            $validated['role'] = 'employee'; // Forzar rol de empleado
            $validated['id_branch'] = $authUser->id_branch; // Mantener en la misma sucursal
        } else {
            abort(403, 'Acceso no autorizado');
        }

        try {
            $user->update($validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente',
                    'user' => $user->fresh()->load('branch')
                ]);
            }

            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el usuario.'
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Error al actualizar el usuario.');
        }
    }

    public function destroy(Request $request, User $user)
    {
        $authUser = Auth::user();
        
        // No puede eliminarse a sí mismo
        if ($user->id === $authUser->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puede eliminar su propia cuenta.'
                ], 422);
            }
            return redirect()->back()->with('error', 'No puede eliminar su propia cuenta.');
        }

        if ($authUser->role === 'owner' || 
            ($authUser->role === 'manager' && $authUser->id_branch === $user->id_branch)) {
            
            try {
                $userName = $user->name;
                $user->delete();
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Usuario '{$userName}' eliminado correctamente."
                    ]);
                }
                
                return redirect()->route('users.index')
                    ->with('success', "Usuario '{$userName}' eliminado correctamente.");
                    
            } catch (\Exception $e) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al eliminar el usuario.'
                    ], 422);
                }
                
                return redirect()->back()->with('error', 'Error al eliminar el usuario.');
            }
        }

        abort(403, 'Acceso no autorizado');
    }
}
