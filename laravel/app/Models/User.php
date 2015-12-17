<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Http\Controllers\BattleController;


class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['username', 'email', 'password', 'picture', 'city', 'about_me',
                            'rapper', 'notifications', 'wins', 'rating', 'device_token'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'email', 'device_token'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'rapper' => 'boolean',
        'notifications' => 'boolean'
    ];

    public function getProfilePictureAttribute()
    {
        return is_null($this->attributes['picture']) ? null : route('data.picture', ['file' => $this->attributes['picture']]);
    }

    /**
     * Get votes of a user
     */
    public function votes()
    {
        return $this->hasMany('App\Models\Vote');
    }

    /**
     * Get battles where this user is rapper 1
     */
    private function battlesRapper1()
    {
        return $this->hasMany('App\Models\Battle', 'rapper1_id');
    }

    /**
     * Get battles where this user is rapper 2
     */
    private function battlesRapper2()
    {
        return $this->hasMany('App\Models\Battle', 'rapper2_id');
    }

    /**
     * Get all battles of this user
     */
    public function battles()
    {
        $ids_r1 = $this->battlesRapper1()->lists('rapper1_id');
        $ids_r2 = $this->battlesRapper2()->lists('rapper2_id');
        return Battle::whereIn('rapper1_id', $ids_r1)->orWhereIn('rapper2_id', $ids_r2);
    }

    /**
     * Get battle requests, created by this user
     */
    public function battleRequestsChallenger()
    {
        return $this->hasMany('App\Models\BattleRequest', 'challenger_id');
    }

    /**
     * Get battle requests, where this user is challenged
     */
    public function battleRequestsChallenged()
    {
        return $this->hasMany('App\Models\BattleRequest', 'challenged_id');
    }

    /**
     * Get all battle requests of this user
     */
    public function battleRequests()
    {
        $ids_challenger = $this->battleRequestsChallenger()->lists('challenger_id');
        $ids_challenged = $this->battleRequestsChallenged()->lists('challenged_id');
        return BattleRequest::whereIn('challenger_id', $ids_challenger)->orWhereIn('challenged_id', $ids_challenged);
    }

    /**
     * Get open battles where this user is rapper 1
     */
    private function openBattlesRapper1()
    {
        return $this->hasMany('App\Models\OpenBattle', 'rapper1_id');
    }

    /**
     * Get open battles where this user is rapper 2
     */
    private function openBattlesRapper2()
    {
        return $this->hasMany('App\Models\OpenBattle', 'rapper2_id');
    }

    /**
     * Get all open battles of this user
     */
    public function openBattles()
    {
        $ids_r1 = $this->openBattlesRapper1()->lists('rapper1_id');
        $ids_r2 = $this->openBattlesRapper2()->lists('rapper2_id');
        return OpenBattle::whereIn('rapper1_id', $ids_r1)->orWhereIn('rapper2_id', $ids_r2);
    }

    /**
     * Scope a query to only include rappers
     */
    public function scopeRapper($query)
    {
        return $query->where('rapper', true);
    }

    /**
     * Scope a query to only contain ratings between $min and $max
     */
    public function scopeRatedBetween($query, $min, $max)
    {
        //$this->updateRating();
        return $query->where('rating', '>=', $min)->where('rating', '<=', $max);
    }

    /**
     * Scope a query to contain users named like $name
     *
     * @param string  $name
     */
    public function scopeNamedLike($query, $name)
    {
        return $query->where('username', 'like', '%'.$name.'%');
    }

    /**
     * Check whether the users device token is null
     */
    public function hasDeviceToken()
    {
        return !is_null($this->device_token);
    }

    /**
     * Make a profile preview containing only certain informations.
     *
     * @return array
     */
    public function profilePreview()
    {
        return [
            'user_id' => $this->id,
            'username' => $this->username,
            'profile_picture' => $this->profile_picture
        ];
    }

    /**
     * Make a profile with all relevant information representing the user.
     *
     * @return array
     */
    public function profile()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'profile_picture' => $this->profile_picture,
            'city' => $this->city,
            'about_me' => $this->about_me,
            'statistics' => ['wins' => $this->wins, 'defeats' => $this->defeats],
            'rapper' => $this->rapper
        ];
    }

    /**
     * Get the settings of the user.
     *
     * @return array
     */
    public function settings()
    {
        return [
            'rapper' => $this->rapper,
            'notifications' => $this->notifications
        ];
    }

    /**
     * update the rating of a user, as well as wins and defeats
     * at the moment a user gets three points for each won battle
     * plus one point for each defeat (for participation)
     */
    public function updateRating()
    {
        $completed = User::battles()->completed()->get();
        $this->wins = 0;
        $this->defeats = 0;

        foreach($completed as $battle){
            if($battle->votes_rapper1 > $battle->votes_rapper2){
                if($battle->rapper1_id == $this->id){
                    $this->wins += 1;
                } else {
                    $this->defeats += 1;
                }
            } else if($battle->votes_rapper1 < $battle->votes_rapper2){
                if($battle->rapper1_id == $this->id){
                    $this->defeats += 1;
                } else {
                    $this->wins += 1;
                }
            }
        }

        $this->rating = $this->wins * 3 + $this->defeats;
        $this->save();
    }
}
