<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenBattle extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rapper1_id', 'rapper2_id', 'phase', 'phase_start', 'beat1_id', 'beat2_id', 
                            'rapper1_round1', 'rapper2_round1', 'rapper1_round2', 'rapper2_round2'];

    /**
     * Get user rapper 1
     */
    public function rapper1()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    /**
     * Get user rapper 2
     */
    public function rapper2()
    {
        return $this->belongsTo('App\Models\User');
    }
    
}
