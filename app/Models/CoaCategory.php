<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoaCategory extends Model
{
    protected $fillable = [
        'account_group_id',
        'name',
    ];

    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }
}

