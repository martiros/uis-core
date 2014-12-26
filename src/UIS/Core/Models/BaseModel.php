<?php

namespace UIS\Core\Models;

use Lang;

abstract class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    const TRUE = '1';
    const FALSE = '0';

    const STATUS_DELETED = '0';
    const STATUS_ACTIVE = '1';
    const STATUS_INACTIVE = '2';

    protected $defaultColumns = '*';

    public function scopeBase($query, $columns = null)
    {
        if ($columns === null) {
            $columns = $this->defaultColumns;
        }
        return $query->get($columns);
    }

    public function scopeCLng($query)
    {
        return $query->where('lng_id', Lang::cLng('id'));
    }

    public function scopeActive($query)
    {
        return $query->where('show_status', BaseModel::STATUS_ACTIVE);
    }


//    public static function all($columns = null)
//    {
//        $instance = new static;
//
//        return $instance->newQuery()->get($columns);
//    }
//
//    protected function getColumnsList($columns = null)
//    {
//
//    }
}