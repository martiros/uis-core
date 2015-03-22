<?php
namespace UIS\Core\Locale;

use UIS\Core\Models\BaseModel;

class Language extends BaseModel
{
    protected $table = 'language';

    protected $fillable = [
        'id',
        'code',
        'name',
        'sort_order',
        'is_default',
        'show_status',
    ];
}
