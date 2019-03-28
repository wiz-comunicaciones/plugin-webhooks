<?php namespace Wiz\Webhooks\Classes;

use Wiz\Webhooks\Models\Hook;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;

class ShellHandler
{
    public static function fire($job, $data)
    {
        try {
            $hook = Hook::findOrFail($hookId);
        } catch (ModelNotFoundException $e) {
            throw $e;
        }

        // Make sure the script is enabled
        if (!$hook->is_enabled) {
            throw new ScriptDisabledException();
        }

        // Run the script and log the output
        $output = shell_exec($hook->script);
        Log::create(['hook_id' => $hook->id, 'output' => $output]);

        // Update our executed_at timestamp
        $hook->executed_at = Carbon::now();
        $hook->save();
    }
}