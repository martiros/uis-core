<?php

namespace UIS\Core\DB\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use \UIS\Core\Models\BaseModel;

class Blueprint extends IlluminateBlueprint
{
    public function showStatus($addLogInfo = true)
    {
        $this->enum('show_status', array(
            BaseModel::STATUS_ACTIVE,
            BaseModel::STATUS_INACTIVE,
            BaseModel::STATUS_DELETED
        ));

        if ($addLogInfo === true) {
            $this->timestamp('created_at')->nullable();
            $this->integer('created_admin_id')->unsigned()->default(0);
            $this->string('created_admin_ip')->default('');

            $this->timestamp('updated_at')->nullable();
            $this->integer('updated_admin_id')->unsigned()->default(0);
            $this->string('updated_admin_ip')->default('');

            $this->timestamp('deleted_at')->nullable();
            $this->integer('deleted_admin_id')->unsigned()->default(0);
            $this->string('deleted_admin_ip')->default('');
        }
    }
}
