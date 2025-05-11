<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Model;


/**
 * @property $name
 * @property $type
 * @property $icon
 * @property $color
 * @property $user_id
 * @property $is_custom
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'type',
        'color',
        'icon',
        'is_custom',
        'user_id'
    ];

   public function scopeIsCustom($query){
       return $query->where('is_custom',true);
   }
}
