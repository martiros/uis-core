<?php

namespace UIS\Core\Foundation;

use DB;

class Profiler
{
    protected $options = [

        /* Max size of profile statistics, pass 0 to set unlimited. */
        'max_size' => 10000,

        /* Set true to enable profiling. */
        'enabled' => false,
    ];

    public function deleteOldData()
    {
        if (!$this->options['max_size']) {
            return;
        }

        $tp = DB::getTablePrefix();
        $sql = "SELECT MAX(id) as max_id FROM `{$tp}app_profile` ";
        $data = DB::select($sql);

        if (empty($data)) {
            return;
        }

        $maxId = $data[0]->max_id;
        $deleteToId = $maxId - $this->options['max_size'];
        $sql = "DELETE FROM `{$tp}app_profile` WHERE `id` < {$deleteToId} ";
        DB::delete($sql);
    }
}
