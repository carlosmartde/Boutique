<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Eliminar historial: solo permitir apertura, no mostrar listado
    public function index()
    {
        $user = Auth::user();
        $cajaAbierta = $user?->cajas()->where('estado', 'abierto')->latest()->first();

        return view('caja.apertura', compact('cajaAbierta'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        // Evitar doble apertura: si ya hay una caja abierta, no permitir crear otra
        $user = Auth::user();
        if ($user->cajas()->where('estado', 'abierto')->exists()) {
            return redirect()->route('caja.apertura')->with('error', 'Ya tienes una caja abierta. Ciérrala antes de abrir una nueva.');
        }

        $user->cajas()->create([
            'monto_inicial' => $request->monto_inicial,
            'estado' => 'abierto',
            'fecha_apertura' => now(),
        ]);

        return redirect()->route('sales.create')->with('success', 'Caja abierta con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Este método puede reservarse para futuras actualizaciones de una caja específica
        return redirect()->route('caja.apertura');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Cerrar la caja abierta del usuario autenticado
     */
    public function cerrar(Request $request)
    {
        $user = Auth::user();
        $caja = $user->cajas()->where('estado', 'abierto')->latest()->first();

        if (!$caja) {
            return redirect()->route('caja.apertura')->with('error', 'No tienes una caja abierta para cerrar.');
        }

        // Si no hay ventas registradas, asegurar que el monto_final no quede null
        $montoFinal = $caja->monto_final ?? $caja->monto_inicial;

        $caja->update([
            'estado' => 'cerrado',
            'fecha_cierre' => now(),
            'monto_final' => $montoFinal,
        ]);

        return redirect()->route('caja.apertura')->with('success', 'Caja cerrada correctamente.');
    }

    /**
     * Mostrar formulario de movimiento (ingreso/retiro)
     */
    public function movimiento()
    {
        $user = Auth::user();
        $caja = $user->cajas()->where('estado', 'abierto')->latest()->first();
        if (!$caja) {
            return redirect()->route('caja.apertura')->with('error', 'Debes abrir una caja para registrar movimientos.');
        }
        return view('caja.movimiento', compact('caja'));
    }

    /**
     * Registrar un movimiento de caja (ingreso/retiro) con autorización de supervisor
     */
    public function guardarMovimiento(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:ingreso,retiro,ajuste',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
            'supervisor_email' => 'required|email',
            'supervisor_password' => 'required|string',
        ]);

        $user = Auth::user();
        $caja = $user->cajas()->where('estado', 'abierto')->latest()->first();
        if (!$caja) {
            return redirect()->route('caja.apertura')->with('error', 'No hay caja abierta.');
        }

        // Validar supervisor sin cambiar la sesión
        $supervisor = User::whereIn('rol', ['admin', 'gerente'])
            ->where('email', $request->supervisor_email)
            ->first();
        if (!$supervisor || !Hash::check($request->supervisor_password, $supervisor->password)) {
            return back()->with('error', 'Credenciales de supervisor inválidas.');
        }

        $monto = (float) $request->monto;
        $montoActual = $caja->monto_final ?? $caja->monto_inicial;
        $nuevoMonto = $montoActual;
        if ($request->tipo === 'ingreso' || $request->tipo === 'ajuste') {
            $nuevoMonto += $monto;
        } else { // retiro
            if ($monto > $montoActual) {
                return back()->with('error', 'Monto de retiro mayor al disponible en caja.');
            }
            $nuevoMonto -= $monto;
        }

        // Registrar movimiento
        CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $user->id,
            'autorizado_por' => $supervisor->id,
            'tipo' => $request->tipo,
            'monto' => $monto,
            'descripcion' => $request->descripcion,
        ]);

        // Actualizar caja
        $caja->update(['monto_final' => $nuevoMonto]);

        return redirect()->route('caja.apertura')->with('success', 'Movimiento registrado correctamente.');
    }

    /**
     * Formulario de arqueo
     */
    public function arqueo()
    {
        $user = Auth::user();
        $caja = $user->cajas()->where('estado', 'abierto')->latest()->first();
        if (!$caja) {
            return redirect()->route('caja.apertura')->with('error', 'Debes abrir una caja para realizar el arqueo.');
        }
        $saldo_teorico = $caja->monto_final ?? $caja->monto_inicial;
        return view('caja.arqueo', compact('caja', 'saldo_teorico'));
    }

    /**
     * Guardar arqueo (saldo real y observación)
     */
    public function guardarArqueo(Request $request)
    {
        $request->validate([
            'saldo_real' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ]);

        $user = Auth::user();
        $caja = $user->cajas()->where('estado', 'abierto')->latest()->first();
        if (!$caja) {
            return redirect()->route('caja.apertura')->with('error', 'No hay caja abierta.');
        }

        $caja->update([
            'saldo_real' => $request->saldo_real,
            'observacion' => $request->observacion,
        ]);

        return redirect()->route('caja.apertura')->with('success', 'Arqueo guardado.');
    }

    /**
     * Cancelar caja (requiere supervisor)
     */
    public function cancelar(Request $request)
    {
        $request->validate([
            'supervisor_email' => 'required|email',
            'supervisor_password' => 'required|string',
            'motivo' => 'nullable|string',
        ]);

        $user = Auth::user();
        $caja = $user->cajas()->where('estado', 'abierto')->latest()->first();
        if (!$caja) {
            return redirect()->route('caja.apertura')->with('error', 'No hay caja abierta para cancelar.');
        }

        $supervisor = User::whereIn('rol', ['admin', 'gerente'])
            ->where('email', $request->supervisor_email)
            ->first();
        if (!$supervisor || !Hash::check($request->supervisor_password, $supervisor->password)) {
            return back()->with('error', 'Credenciales de supervisor inválidas.');
        }

        // Registrar movimiento de cancelación (informativo)
        CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $user->id,
            'autorizado_por' => $supervisor->id,
            'tipo' => 'cancelacion',
            'monto' => 0,
            'descripcion' => $request->motivo,
        ]);

        // Marcar caja como cancelada y cerrarla
        $caja->update([
            'cancelada' => true,
            'cancel_autorizado_por' => $supervisor->id,
            'cancelado_en' => now(),
            'estado' => 'cerrado',
            'fecha_cierre' => now(),
            'monto_final' => $caja->monto_final ?? $caja->monto_inicial,
        ]);

        return redirect()->route('caja.apertura')->with('success', 'Caja cancelada correctamente.');
    }
}
