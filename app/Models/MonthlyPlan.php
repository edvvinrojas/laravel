<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPlan extends Model
{
    protected $table = 'monthly_plans';

    protected $fillable = [
        'client_id', 'branch_id', 'area_id', 'ticket_id',
        'service_type_id', 'attendance_status', 'description', 'visit_date', 'created_by',
    ];

    protected $casts = ['visit_date' => 'datetime'];

    public function client()      { return $this->belongsTo(Client::class); }
    public function branch()      { return $this->belongsTo(Branch::class); }
    public function area()        { return $this->belongsTo(Area::class); }
    public function ticket()      { return $this->belongsTo(Ticket::class); }
    public function serviceType() { return $this->belongsTo(ServiceType::class, 'service_type_id'); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function users()       { return $this->belongsToMany(User::class, 'monthly_plan_users'); }
}
