<?php namespace Wiz\Webhooks\Jobs;

use Wiz\Webhooks\Models\RequestData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Artisan;

class ConsoleHandler
{
    public static function fire($job, $data)
    {
        # retrieving $requestDataId
        try {
            $requestDataObj = RequestData::find($data['request_id']);
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

        trace_log($output);

        \Wiz\Webhooks\Models\Log::create(['hook_id' => $requestDataObj->hook_id, 'output' => $output]);

        // Update our executed_at timestamp
        $hook->executed_at = Carbon::now();
        $hook->hook->save();
    }
}