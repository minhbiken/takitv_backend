<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoImportPerson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'person:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic import person when posting';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // Replace this with the actual command you want to run
        $command = '/home/vuejs/backend/import_person.sh 2198774 1017507 movie';

        // Execute the Bash script
        $output = shell_exec($command);

        // Output the result
        $this->info($output);
    }
}
