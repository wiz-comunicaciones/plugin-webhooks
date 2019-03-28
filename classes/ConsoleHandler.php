<?php namespace Wiz\Webhooks\Classes;

use Wiz\Webhooks\Models\RequestData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Log;

class ConsoleHandler
{
    public static function fire($requestDataId)
    {
        try {
            $requestDataObj = RequestData::find($requestDataId);
        } catch (ModelNotFoundException $e) {
            throw $e;
        }

        $hook = $requestDataObj->hook;

        // Make sure the script is enabled
        if (!$hook->is_enabled) {
            throw new ScriptDisabledException();
        }

        // Run the script and log the output
        $output = Artisan::call($hook->script, ['request_data' => $requestDataObj->request_data]);
        Log::create(['hook_id' => $requestDataObj->hook_id, 'output' => $output]);

        // Update our executed_at timestamp
        $hook->executed_at = Carbon::now();
        $hook->hook->save();
    }
}