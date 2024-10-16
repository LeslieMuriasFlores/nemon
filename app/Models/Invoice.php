<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['total_energy_cost', 'total_power_cost', 'total_invoice', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}