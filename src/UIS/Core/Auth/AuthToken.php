<?php

namespace UIS\Core\Auth;

use Carbon\Carbon;
use UIS\Core\Models\BaseModel;
use Illuminate\Support\Str;

class AuthToken extends BaseModel implements AuthTokenContract
{
    protected $table = 'auth_token';

    public $timestamps = false;

    protected $fillable = [
        'token',
        'user_id',
        'expire_date'
    ];

    protected $dates = [
        'expire_date'
    ];

    public function getTokenIdentifier()
    {
        return $this->attributes['id'];
    }

    public function getToken()
    {
        return $this->attributes['token'];
    }

    public function isActiveToken()
    {
        $now = new Carbon();
        if ($now->lt($this->expire_date)) {
            return true;
        }
        return false;
    }
}
