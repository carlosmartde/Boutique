<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index()
    {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        $users = User::all();
        return view('users.management', compact('users'));
    }

    public function toggleStatus($id)
    {
        if (Auth::user()->rol !== 'admin') {
            return redirect()->route('sales.create')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        try {
            $user = User::findOrFail($id);
            
            // Evitar que un admin se desactive a sí mismo
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivar tu propia cuenta.'
                ], 403);
            }

            // Usar una transacción para asegurar que ambas operaciones se completen
            DB::transaction(function() use ($user) {
                $wasActive = $user->status;
                $user->status = !$wasActive;
                $user->save();

                // Si el usuario está siendo desactivado, eliminar sus sesiones activas
                if ($wasActive) {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }
            });

            $status = $user->status ? 'activado' : 'desactivado';
            
            return response()->json([
                'success' => true,
                'message' => "El usuario {$user->name} ha sido {$status} correctamente.",
                'newStatus' => $user->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ha ocurrido un error al actualizar el estado del usuario.'
            ], 500);
        }
    }
}
