<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use DB;


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

    /**
     * Get route to the users profile picture.
     *
     * @return null|string
     */
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
        $ids_challenger = $this->battleRequestsChallenger()->lists('id')->toArray();
        $ids_challenged = $this->battleRequestsChallenged()->lists('id')->toArray();
        return BattleRequest::whereIn('id', array_merge($ids_challenger, $ids_challenged));
    }

    /**
     * Checks whether a user has a battle request agains $user
     */
    public function hasBattleRequestAgainst(User $user)
    {
        // possibly naive solution, other one had really ugly bugs
        $cnt1 = BattleRequest::where('challenger_id', $this->id)->where('challenged_id', $user->id)->count();
        $cnt2 = BattleRequest::where('challenged_id', $this->id)->where('challenger_id', $user->id)->count();
        return $cnt1 > 0 || $cnt2 > 0;
    }

    /**
     * Get all users who do not have a battle request against $user
     */
    public function scopeNoBattleRequestsAgainst($query, User $user)
    {
        // get opponents of $user
        $challenger = $user->battleRequests()->lists('challenger_id')->toArray();
        $challenged = $user->battleRequests()->lists('challenged_id')->toArray();
        $array = array_merge($challenger, $challenged);
        return $query->whereNotIn('id', $array);
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
        $ids_rapper1 = $this->openBattlesRapper1()->lists('id')->toArray();
        $ids_rapper2 = $this->openBattlesRapper2()->lists('id')->toArray();
        return OpenBattle::whereIn('id', array_merge($ids_rapper1, $ids_rapper2));
    }

    /**
     * Check if this user has an open battle $user
     */
    public function hasOpenBattleAgainst(User $user)
    {
        //$cnt = $this->openBattles()->where('rapper1_id', $user->id)->orWhere('rapper2_id', $user->id)->count();
        //return $cnt > 0;
        // possibly naive solution, other one had really ugly bugs
        $cnt1 = OpenBattle::where('rapper1_id', $this->id)->where('rapper2_id', $user->id)->open()->count();
        $cnt2 = OpenBattle::where('rapper2_id', $this->id)->where('rapper1_id', $user->id)->open()->count();
        return $cnt1 > 0 || $cnt2 > 0;
    }

    /**
     * Get all users woh $user has no open battle against
     */
    public function scopeNoOpenBattleAgainst($query, User $user)
    {
        // get opponents of $user
        $rapper1 = $user->openBattles()->open()->lists('rapper1_id')->toArray();
        $rapper2 = $user->openBattles()->open()->lists('rapper2_id')->toArray();

        return $query->whereNotIn('id', array_merge($rapper1, $rapper2));
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
        if($min > $max){
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }
        return $query->whereBetween('rating', [$min, $max]);
    }

    /**
     * Get all users except $user
     */
    public function scopeNot($query, User $user)
    {
        return $query->where('id', '<>', $user->id);
    }

    /**
     * Get all rappers who don't have open battles or battle requests to $user
     */
    public function scopeValidOpponentFor($query, User $user)
    {
        return User::rapper()->not($user)->noOpenBattleAgainst($user)->noBattleRequestsAgainst($user);
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
            'profile_picture' => $this->getProfilePictureAttribute()
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
            'profile_picture' => $this->getProfilePictureAttribute(),
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
}
