<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A till: one selling PC with its own cash drawer. A branch can have several.
 */
class Register extends Model
{
    protected $fillable = ['branch_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Browser cookie that remembers which till a PC is. */
    public const DEVICE_COOKIE = 'pos_register_id';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function openShift(): ?Shift
    {
        return $this->shifts()->with('user')->where('status', 'open')->latest('started_at')->first();
    }

    public function label(): string
    {
        return ($this->branch?->name ?? 'Branch') . ' · ' . $this->name;
    }

    /**
     * Active tills of a branch. Every branch always has at least "Till 1".
     */
    public static function forBranch(Branch $branch)
    {
        if (!static::where('branch_id', $branch->id)->exists()) {
            static::create(['branch_id' => $branch->id, 'name' => 'Till 1']);
        }

        return static::where('branch_id', $branch->id)->where('is_active', true)->orderBy('id')->get();
    }
}
