<?php 
namespace App\Models;

use App\Models\BaseModel;

class Car extends BaseModel
{
     protected $fillable = ['make', 'model', 'year'];
     public $timestamps = false;
}
