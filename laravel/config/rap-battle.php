<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Voting Period Configuration
    |--------------------------------------------------------------------------
    |
    | This sets the time in hours a battle is voteable after it is created
    | default: 24
    |
    */

    'votingperiod' => 24,

    /*
    |--------------------------------------------------------------------------
    | Trending Period Configuration
    |--------------------------------------------------------------------------
    |
    | This sets the time in hours a battle is considered for trending battles
    | default: 168 (1 week)
    |
    */

    'trendingperiod' => 168,

    /*
    |--------------------------------------------------------------------------
    | Video Size / Resolution
    |--------------------------------------------------------------------------
    |
    | This sets the video size
    | default width: 1920
    | defualt height: 1080
    |
    */

    'video_width' => 1920,
    'video_height' => 1080,

    /*
    |--------------------------------------------------------------------------
    | Video Bitrate
    |--------------------------------------------------------------------------
    |
    | This sets the video bitrate (in kbits)
    | default: 1000
    |
    */

    'video_bitrate' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Audio Bitrate
    |--------------------------------------------------------------------------
    |
    | This sets the audio bitrate (in kbits)
    | default: 128
    |
    */

    'audio_bitrate' => 128,

    /*
    |--------------------------------------------------------------------------
    | Audio codec
    |--------------------------------------------------------------------------
    |
    | This sets the audio codec for video conversion
    | default: 'libmp3lame'
    |
    */

    'audio_codec' => 'libmp3lame',

];
