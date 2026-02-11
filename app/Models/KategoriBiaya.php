<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriBiaya extends Model
{
    //
    protected $guarded = ['id'];
    use SoftDeletes;
    
}
