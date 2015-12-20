<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OpenBattle extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rapper1_id', 'rapper2_id', 'phase', 'beat1_id', 'beat2_id', 'rapper1_round1',
                            'rapper2_round1', 'rapper1_round2', 'rapper2_round2'];

    /**
     * Set the phase while updating the timer.
     */
    public function setPhaseAttribute($phase)
    {
        $this->attributes['phase'] = $phase;
        $this->attributes['phase_start'] = Carbon::now();

        //TODO start phase timer
    }

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

    /**
     * Start an open battle.
     *
     * @param \App\Models\User  $rapper1
     * @param \App\Models\User  $rapper2
     */
    public function start(User $rapper1, User $rapper2)
    {
        $this->rapper1()->associate($rapper1);
        $this->rapper2()->associate($rapper2);
        $this->phase = 1;
    }
}
