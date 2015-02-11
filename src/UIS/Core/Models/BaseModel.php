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
        return $query->where('lng_id', Lang::language()->id);
    }

    public function scopeActive($query)
    {
        return $query->where('show_status', BaseModel::STATUS_ACTIVE);
    }

    public function scopeOrdered($query, $mode = 'ASC')
    {
        return $query->orderBy('sort_order', $mode)->orderBy('id', 'DESC');
    }


//    protected function asDateTime($value)
//    {
//        die();
//        if ($value === '0000-00-00 00:00:00') {
//            return null;
//        }
//        return parent::asDateTime($value);
//    }

public function scopePaginateBase($query, $perPage = null, $columns = null)
    {
        $paginator = $query->getQuery()->getConnection()->getPaginator();

//        if (isset($query->groups))
//        {
//            return $this->groupedPaginate($paginator, $perPage, $columns);
//        }
//        else
//        {
//            return $this->ungroupedPaginate($paginator, $perPage, $columns);
//        }

        $total = $query->getQuery()->getPaginationCount();

        // Once we have the paginator we need to set the limit and offset values for
        // the query so we can get the properly paginated items. Once we have an
        // array of items we can create the paginator instances for the items.
        $page = $paginator->getCurrentPage($total);

        $query->getQuery()->forPage($page, $perPage);

        if ($columns === null) {
            $columns = $this->defaultColumns;
        }

        return $paginator->make($query->get($columns)->all(), $total, $perPage);



        $perPage = $perPage ?: 10;

        $paginator = $query->getQuery()->getConnection()->getPaginator();

        $total = $query->getQuery()->getPaginationCount();

        // Once we have the paginator we need to set the limit and offset values for
        // the query so we can get the properly paginated items. Once we have an
        // array of items we can create the paginator instances for the items.
        $page = $paginator->getCurrentPage($total);

        $query->getQuery()->forPage($page, $perPage);


        return $paginator->make($this->get($columns)->all(), $total, $perPage);

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