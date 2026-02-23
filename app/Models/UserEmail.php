<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmail extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'is_primary',
    ];
    protected $casts = ['is_primary' => 'bool'];

    /**
     * Relation to user
     *
     * @return BelongsTo
     */
    public function userRelation(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
