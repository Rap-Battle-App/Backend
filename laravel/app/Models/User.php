<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * TODO: add other fields, if necessary
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'city', 'about_me'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'email'];

    /**
     * Get votes of a user
     */
    public function votes()
    {
        returns $this->hasMany('App\Models\Vote');
    }

    /**
     * Get battles where this user is rapper 1
     */
    public function battlesRapper1()
    {
        return $this->hasMany('App\Models\Battle', 'rapper1_id');
    }

    /**
     * Get battles where this user is rapper 2
     */
    public function battlesRapper2()
    {
        return $this->hasMany('App\Models\Battle', 'rapper2_id');
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
     * Get open battles where this user is rapper 1
     */
    public function openBattlesRapper1()
    {
        return $this->hasMany('App\Models\OpenBattle', 'rapper1_id');
    }

    /**
     * Get open battles where this user is rapper 2
     */
    public function openBattlesRapper2()
    {
        return $this->hasMany('App\Models\OpenBattle', 'rapper2_id');
    }

    /**
     * Scope a query to only include rappers
     */
    public function scopeRapper($query)
    {
        return $query->where('rapper', 1);
    }

    /**
     * Scope a query to only contain the top $num rappers by the rating
     */
    public function scopeRatedBetween($query, $min, $max)
    {
        return $query->where('rating', '>=', $min)->where('rating', '<=', $max);
    }

}
