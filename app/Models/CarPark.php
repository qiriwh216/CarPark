<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarPark extends Model
{
   protected $guarded = [];
   protected $casts = [
       'is_sale' => 'boolean'
   ];

   public function user(){
       $this->belongsTo(User::class);
   }

   public function community(){
      $this->belongsTo(Community::class); 
   }
   
}
