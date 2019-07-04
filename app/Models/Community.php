<?php

namespace App\Models;
class Community extends Model {
    protected $guarded = [];

    public function carParks(){
       $this->hasMany(CarPark::class); 
    }
    
}
