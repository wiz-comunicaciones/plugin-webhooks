<?php namespace Wiz\Webhooks\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Wiz\Webhooks\Models\RequestData;

class Example extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'wiz:webhooks.example';

    /**
     * @var string The console command description.
     */
    protected $description = 'Outputs message with received data for testing purposes.';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        trace_log('Running the example console command.');

        # Get the request data
        $request = RequestData::findOrfail($this->argument('request_id'));

        # Work with the request data. You will have access to the hook object in $request->hook
        $this->output->writeln('Executed test console command');
        $this->output->writeln('Hook: ' . $request->hook->name . ' (hookId: ' . $request->hook_id . ')');
        $this->output->writeln('Received data:');
        $this->output->write(json_encode($request->request_data), true);
    }

    protected function getArguments()
    {
        return [
            ['request_id', InputArgument::REQUIRED, 'The requestData model id.'],
        ];
    }
}