<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'comercial_name', 'rfc', 'address',
        'colonia', 'zip_code', 'city', 'user_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function contacts() { return $this->hasMany(Contact::class); }
    public function creator()  { return $this->belongsTo(User::class, 'user_id'); }
    public function branches() { return $this->hasMany(Branch::class); }
    public function rents()    { return $this->hasMany(Rent::class); }
    public function sales()    { return $this->hasMany(Sale::class); }
    public function billings() { return $this->hasMany(Billing::class); }
    public function tickets()  { return $this->hasMany(Ticket::class); }
}
