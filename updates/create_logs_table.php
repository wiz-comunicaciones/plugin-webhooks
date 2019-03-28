<?php namespace Wiz\Webhooks\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateLogsTable extends Migration
{

    public function up()
    {
        Schema::create('wiz_webhooks_logs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('hook_id')->unsigned()->index();
            $table->longText('output')->nullable();
            $table->text('referrer')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wiz_webhooks_logs');
    }

}
