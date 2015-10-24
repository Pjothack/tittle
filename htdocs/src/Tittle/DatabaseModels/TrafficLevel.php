<?php

namespace Tittle\DatabaseModels;

class TrafficLevel extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;

    public function location()
    {
        return $this->belongsTo('Location');
    }
}
