<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'battle_id', 'rapper_number'];
    
    /**
     * Get the user who voted
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    /**
     * Get the battle that is voted on
     */
    public function battle()
    {
        return $this->belongsTo('App\Models\Battle');
    }
    
}
