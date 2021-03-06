<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * @var string
     * Table for Database
     */
    protected $table = 'Post';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'category_id',
    ];

    /**
     * Relationship One To Many / Many To One
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Relationship One To Many / Many To One
     */
    public function category()
    {
        return $this->belongsTo('App\Category','category_id');
    }
}
