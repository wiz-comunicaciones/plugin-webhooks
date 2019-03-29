<?php namespace Wiz\Webhooks\Jobs;

use Wiz\Webhooks\Models\RequestData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Artisan;

class ConsoleHandler
{
    public static function fire($job, $data)
    {
        trace_log('Fired event');
        trace_log($data);
        # retrieving $requestDataId
        try {
            $requestDataObj = RequestData::find($data['request_id']);
        } catch (ModelNotFoundException $e) {
            throw $e;
        }

        trace_log('Got request data object ok');

        $hook = $requestDataObj->hook;

        // Make sure the script is enabled
        if (!$hook->is_enabled) {
            throw new ScriptDisabledException();
        }

        trace_log('Hook found and ok');

        // Run the script and log the output
        Artisan::call($hook->script, ['request_data' => $requestDataObj->request_data]);

        trace_log('Artisan called');

        \Wiz\Webhooks\Models\Log::create(['hook_id' => $requestDataObj->hook_id, 'output' => Artisan::output()]);

        trace_log('Log created');

        // Update our executed_at timestamp
        $hook->executed_at = Carbon::now();
        $hook->hook->save();

        trace_log('Deleting job');
        $job->delete();
        trace_log('Job deleted');
    }
}