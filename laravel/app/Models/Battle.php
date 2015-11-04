<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    /**
     * The database table used by the model.
     * implied by class name
     * @var string
     */
    /* protected $table = 'battles'; */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rapper1_id', 'rapper2_id', 'video'];

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
    public function votes(){
        return $this->hasMany('App\Models\Votes');
    }

    /**
     * Scope a query to only contain trending battles
     */
    public function scopeTrending($query)
    {
        // TODO: implement this
        //return $query->
    }

    /**
     * Scope a query to only contain battles that are open for voting
     */
    public function scopeOpenVoting($query)
    {
        // TODO: implement this
        //return $query->;
    }

}
