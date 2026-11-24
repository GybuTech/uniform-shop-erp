<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = ['sync_token_hash'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'branch_name', 'name');
    }

    /** Tills (selling PCs with their own cash drawer) at this branch. */
    public function registers()
    {
        return $this->hasMany(Register::class)->orderBy('id');
    }
}
