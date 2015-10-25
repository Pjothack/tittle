<?php

namespace Tittle\DatabaseModels;

class Location extends \Illuminate\Database\Eloquent\Model
{
    public function discounts()
    {
        return $this->hasMany('Tittle\DatabaseModels\Discount');
    }
}
