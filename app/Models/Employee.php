<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'lastname',
        'dni',
        'birth_date',
        'photo',
        'user_id'
    ];

    /**
     * Get the user record associated with the post.
     */
    public function job()
    {
        return $this->belongsTo(JobTitle::class);
    }
}
