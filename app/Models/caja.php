<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'monto_inicial',
        'monto_final',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'saldo_real',
        'observacion',
        'cancelada',
        'cancel_autorizado_por',
        'cancelado_en',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function cancelAutorizadoPor()
    {
        return $this->belongsTo(User::class, 'cancel_autorizado_por');
    }
}

