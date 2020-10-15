<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * @package App
 * @property string $first_name
 * @property string $second_name
 * @property string $email
 * @property string $page_uid
 *
 */
class User extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'second_name',
        'email',
        'page_uid'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
