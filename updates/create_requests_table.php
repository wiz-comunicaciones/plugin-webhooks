<?php namespace Wiz\Webhooks\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRequestsTable extends Migration
{

    public function up()
    {
        Schema::create('wiz_webhooks_requests', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('hook_id')->unsigned()->index();
            $table->longText('request_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wiz_webhooks_requests');
    }

}
