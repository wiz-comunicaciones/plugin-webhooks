<?php namespace Wiz\Webhooks\Console;

use Illuminate\Console\Command;
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
        $start = microtime(true);

        # Start logging
        Log::info('Started Test console command...');
        Log::info($this->argument('request_data'));

        $this->output->writeln('Executed test console command');
        $this->output->writeln('Received data:');
        $this->output->write(json_encode($this->argument('request_data')));

        $end = microtime(true);
        Log::info('Test command finished. Ellapsed time: ' . ($end - $start) . ' seconds.');

    }
}