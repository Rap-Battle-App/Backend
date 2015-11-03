<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleRequest extends Model
{
    /**
     * The database table used by the model.
     * implied by class name
     * @var string
     */
    protected $table = 'battle_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['challenger_id', 'challenged_id'];

    /**
     * Get the user who created the challenge
     */
    public function challenger()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    /**
     * Get the user who is challenged
     */
    public function challenged()
    {
        return $this->belongsTo('App\Models\User');
    }
    
}