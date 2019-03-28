<?php namespace Wiz\Webhooks\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateHooksTable extends Migration
{

    public function up()
    {
        Schema::table('wiz_webhooks_hooks', function($table) {
            $table->string('type')->default('shell');
        });
    }

    public function down()
    {
        Schema::table('wiz_webhooks_hooks', function($table) {
            $table->dropColumn('type');
        });
    }

}
