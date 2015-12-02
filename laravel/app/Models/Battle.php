<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Battle extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rapper1_id', 'rapper2_id', 'video', 'votes_rapper1', 'votes_rapper2'];

    /**
     * Get the user rapper 1
     */
    public function rapper1()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    /**
     * Get the user rapper 2
     */
    public function rapper2()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    /**
     * Get votes
     */
    public function votes()
    {
        return $this->hasMany('App\Models\Vote');
    }

    /**
     * Scope a query to only contain trending battles
     */
    public function scopeTrending($query)
    {
        $trendingperiod = config('rap-battle.trendingperiod', 168);

        // battles after this date will be considered for trending
        $timeoldest = new Carbon();
        $timeoldest->subHours($trendingperiod);

        return $query->where('created_at', '>=', $timeoldest->toDateTimeString())->orderBy(
                        DB::raw('votes_rapper1 + votes_rapper2'), 'desc');
    }

    /**
     * Scope a query to only contain battles that are open for voting
     */
    public function scopeOpenVoting($query)
    {
        $votingperiod = config('rap-battle.votingperiod', 24);

        // battles before this date are closed:
        $timeoldest = new Carbon();
        $timeoldest->subHours($votingperiod);

        return $query->where('created_at', '>=', $timeoldest->toDateTimeString());
    }

    /**
     * Scope a query to only contain battles that are closed for voting
     */
    public function scopeCompleted($query)
    {
        $votingperiod = config('rap-battle.votingperiod', 24);

        // battles before this date are closed:
        $timeoldest = new Carbon();
        $timeoldest->subHours($votingperiod);

        return $query->where('created_at', '<', $timeoldest->toDateTimeString());
    }

}
