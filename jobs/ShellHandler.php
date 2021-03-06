<?php namespace Wiz\Webhooks\Jobs;

use Wiz\Webhooks\Models\Hook;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShellHandler
{
    public static function fire($job, $data)
    {
        try {
            $hook = Hook::findOrFail($data['hook_id']);
        } catch (ModelNotFoundException $e) {
            throw $e;
        }

        // Make sure the script is enabled
        if (!$hook->is_enabled) {
            throw new ScriptDisabledException();
        }

        // Run the script and log the output
        $output = shell_exec($hook->script);
        \Wiz\Webhooks\Models\Log::create(['hook_id' => $hook->id, 'output' => $output]);

        // Update our executed_at timestamp
        $hook->executed_at = Carbon::now();
        $hook->save();
    }
}