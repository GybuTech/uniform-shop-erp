<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'branch_id',
        'branch_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Whether this account has recorded business transactions. Such accounts must be
     * deactivated rather than deleted: sales, returns and stock entries reference the
     * user, and deleting the user would also cascade-delete their shift history.
     */
    public function hasTradingHistory(): bool
    {
        return Sale::where('user_id', $this->id)->exists()
            || Shift::where('user_id', $this->id)->exists()
            || SaleReturn::where('user_id', $this->id)->exists()
            || CustomerPayment::where('user_id', $this->id)->exists()
            || StockEntry::where('user_id', $this->id)->exists();
    }
}
