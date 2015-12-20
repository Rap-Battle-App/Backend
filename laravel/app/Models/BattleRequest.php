<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['challenger_id', 'challenged_id'];

    /**
     * Get the user who created the request.
     */
    public function challenger()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the user who is challenged.
     */
    public function challenged()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the JSON form of a request where the user is the challenger.
     *
     * @return array
     */
    public function toJSON_Challenger()
    {
        return [
            'id' => $this->id,
            'opponent' => $this->challenged->profilePreview(),
            'date' => $this->created_at
        ];
    }

    /**
     * Get the JSON form of a request where the user is the challenged user.
     *
     * @return array
     */
    public function toJSON_Challenged()
    {
        return [
            'id' => $this->id,
            'opponent' => $this->challenger->profilePreview(),
            'date' => $this->created_at
        ];
    }
}
