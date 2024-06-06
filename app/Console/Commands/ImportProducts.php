<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use DateTime;


class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products
                            {csv : Absolute path to products data file.}
                            {--test : Run without altering DB.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processed = 0;
        $successful = 0;
        $skipped = 0;
        $skippedCodes = [];
        $errors = [];
        $path = $this->argument('csv');
        $test = $this->option('test');

        if (!file_exists($path)) {
            $this->line("The file is not found.");
            return;
        }

        // skipping header row
        $rowNumber = 1;
        $file = fopen($path, 'r');
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;
            $processed++;

            if (count($row) != 6) {
                $skipped++;
                $errors[] = "Row $rowNumber is malformed.";
                continue;
            }

            $product = new Product;
            $product->code = (string) $row[0];
            $product->name = (string) $row[1];
            $product->description = (string) $row[2];
            $product->stock = (int) $row[3];
            $product->price = (float) $row[4];
            $product->discontinued = $row[5] == 'yes' ? new DateTime : null;

            if ($product->shouldBeSkipped()) {
                $skippedCodes[] = $product->code;
                $skipped++;
                continue;
            }

            if (!$test) {
                $product->save();
            }

            $successful++;
        }

        $this->line("Processed: $processed");
        $this->line("Successful: $successful");
        $outputForSkipped = "Skipped: $skipped";

        if ($skippedCodes) {
            $outputForSkipped .= ' (' . implode(', ', $skippedCodes) . ')';
        }

        $this->line($outputForSkipped);
        $this->line(implode("\n", $errors));
        fclose($file);
    }
}
