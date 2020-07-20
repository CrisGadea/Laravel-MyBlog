<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * @var string
     * Table for Database
     */
    protected $table = 'Category';

    /**
     * Relationship One to Many
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany('App/Post');
    }
}
