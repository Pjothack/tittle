<?php

namespace Tittle\DatabaseModels;

class Location extends \Illuminate\Database\Eloquent\Model
{
    public function discounts()
    {
        return $this->hasMany('Tittle\DatabaseModels\Discount');
    }

    public function category()
    {
        return $this->belongsTo('Tittle\DatabaseModels\Category', 'id');
    }

    public function trafficLevels()
    {
        return $this->hasMany('Tittle\DatabaseModels\TrafficLevel');
    }
}
