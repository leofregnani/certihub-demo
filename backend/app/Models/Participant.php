<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = ['name', 'email', 'document', 'phone'];

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
