<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{    protected $fillable = [
        'sale_id',
        'invoice_number',
        'customer_name',
        'customer_nit',
        'customer_address',
        'customer_phone',
        'customer_email',
        'payment_method',
        'total',
        'is_cf'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function generateInvoiceNumber()
    {
        // Format: FEL-YYYYMMDD-XXXX where XXXX is a sequential number
        $date = now()->format('Ymd');
        $lastInvoice = self::where('invoice_number', 'like', "FEL-$date-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
            
        if ($lastInvoice) {
            $sequence = intval(substr($lastInvoice->invoice_number, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return "FEL-$date-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
