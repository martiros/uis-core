<?php

namespace UIS\Core\Locale;

use UIS\Core\Models\BaseModel;

class ApplicationLanguage extends BaseModel
{
    protected $table = 'application_language';

    protected $hidden = [
        'application',
        'created_at',
        'created_admin_id',
        'created_admin_ip',
        'updated_at',
        'updated_admin_id',
        'updated_admin_ip',
        'deleted_at',
        'deleted_admin_id',
        'deleted_admin_ip',
        'show_status',
    ];
}
