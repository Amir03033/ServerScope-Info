<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cookieless page counter: path, day and number of views. Nothing about
 * visitors is stored.
 */
class PageView extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
