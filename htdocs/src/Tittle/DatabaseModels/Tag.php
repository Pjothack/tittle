<?php

namespace Tittle\DatabaseModels;

class Tag extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo('Tittle\DatabaseModels\Category');
    }
}
