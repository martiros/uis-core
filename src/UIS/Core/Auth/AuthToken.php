<?php
namespace UIS\Core\Auth;

use UIS\Core\Models\BaseModel;

class AuthToken extends BaseModel
{
    protected $table = 'auth_token';

    public $timestamps = false;

    protected $fillable = [
        'token',
        'user_id',
        'expire_date'
    ];
}
