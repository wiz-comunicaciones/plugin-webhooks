<?php namespace Wiz\Webhooks\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Log;

class Test extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'wiz:webhooks.test';

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
        trace_log('Command is being executed');

        $start = microtime(true);

        # Start logging
        Log::info('Started Test console command...');
        Log::info($this->argument('request_id'));

        $this->output->writeln('Executed test console command');
        $this->output->writeln('Received data:');
        $this->output->write(json_encode($this->argument('request_id')));

        $end = microtime(true);
        Log::info('Test command finished. Ellapsed time: ' . ($end - $start) . ' seconds.');

        trace_log('Command ended');
    }

    protected function getArguments()
    {
        return [
            ['request_id', InputArgument::REQUIRED, 'The requestData model id.'],
        ];
    }
}