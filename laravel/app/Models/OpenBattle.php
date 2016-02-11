<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Storage;

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['phase_start'];

    /**
     * Set the phase while updating the timer.
     */
    public function setPhaseAttribute($phase)
    {
        $this->attributes['phase'] = $phase;
        $this->attributes['phase_start'] = Carbon::now()->toDateTimeString();

        //TODO start phase timer
    }

    /**
     * Returns true, if the OpenBattle is open
     */
    public function isOpen(){
        $start = Carbon::parse($this->phase_start);

        if($this->phase == 1){
            $startMin = Carbon::now()->subHours(config('rap-battle.phase1time', 24));
            return $start->gt($startMin);
        } else if($this->phase == 2){
            $startMin = Carbon::now()->subHours(config('rap-battle.phase2time', 24));
            return $start->gt($startMin);
        } else return false;
    }

    public function scopeOpen($query){
        $start = Carbon::parse($this->phase_start);
        $startMinPhase1 = Carbon::now()->subHours(config('rap-battle.phase1time', 24));
        $startMinPhase2 = Carbon::now()->subHours(config('rap-battle.phase2time', 24));

        $query1 = $query;
        $query2 = clone $query;
        $ids1 = $query1->where('phase', 1)->where('phase_start', '>=',
                $startMinPhase1->toDateTimeString())->lists('id')->toArray();
        $ids2 = $query2->where('phase', 2)->where('phase_start', '>=',
                $startMinPhase2->toDateTimeString())->lists('id')->toArray();

        return OpenBattle::whereIn('id', array_merge($ids1, $ids2));
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
     * Remove model from database and delete video files
     */
    public function delete()
    {
        // clean filesystem
        Storage::disk('videos')->delete($this->rapper1_round1);
        Storage::disk('videos')->delete($this->rapper2_round1);
        Storage::disk('videos')->delete($this->rapper1_round2);
        Storage::disk('videos')->delete($this->rapper2_round2);

        parent::delete();
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
        $this->setPhaseAttribute(1);
    }

    /**
     * Gets the URL to a video file
     */
    private function makeRoundUrl($key)
    {
        return is_null($this->attributes[$key]) ? null : route('data.video', ['file' => $this->attributes[$key]]);
    }

    /**
     * Gets the phase info
     */
    private function phaseInfo($rapperNumber)
    {
        $rapper = 'rapper'.$rapperNumber;
        $opponent = 'rapper'.($rapperNumber == 1 ? 2 : 1);

        $phaseInfo = [
            'time_left' => config('rap-battle.phase'.$this->phase.'time') * 3600 - $this->phase_start->diffInSeconds(),
            'round1_url' => $this->makeRoundUrl($rapper.'_round1')
        ];

        if ($this->phase == 2) {
            $phaseInfo['beat_id'] = $rapperNumber == 1 ? $this->beat2_id : $this->beat1_id;
            $phaseInfo['opponent_round1_url'] = $this->makeRoundUrl($opponent.'_round1');
            $phaseInfo['round2_url'] = $this->makeRoundUrl($rapper.'_round2');
        }

        return $phaseInfo;
    }

    /**
     * Check whether the first round is complete (both videos set)
     */
    public function hasFirstRounds()
    {
        return !empty($this->rapper1_round1)
            && !empty($this->rapper2_round1);
            /*&& $this->phase >= 1;*/
    }

    /**
     * Check whether all rounds are complete (all videos set)
     */
    public function hasAllRounds()
    {
        return $this->hasFirstRounds()
            && !empty($this->rapper1_round2)
            && !empty($this->rapper2_round2)
            && $this->phase >= 2;
    }

    /**
     *
     */
    public function toJSON_Rapper1()
    {
        return [
            'id' => $this->id,
            'opponent' => $this->rapper2->profilePreview(),
            'phase' => $this->phase,
            'info' => $this->phaseInfo(1)
        ];
    }

    /**
     *
     */
    public function toJSON_Rapper2()
    {
        return [
            'id' => $this->id,
            'opponent' => $this->rapper1->profilePreview(),
            'phase' => $this->phase,
            'info' => $this->phaseInfo(2)
        ];
    }
}
