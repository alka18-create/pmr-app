<?php

namespace App\Models;

use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'level'])]
class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory;

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function memberPositions(): HasMany
    {
        return $this->hasMany(MemberPosition::class);
    }
}
