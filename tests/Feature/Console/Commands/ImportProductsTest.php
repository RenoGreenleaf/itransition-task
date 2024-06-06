<?php

namespace Tests\Feature\Console\Commands;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing;
use Tests\TestCase;

class ImportProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * File with product data should be present.
     */
    public function test_absent_file(): void
    {
        $this
            ->artisan("app:import-products /tmp/products.csv")
            ->expectsOutput('The file is not found.');
    }

    /**
     * Data from a CSV should be stored in the DB.
     */
    public function test_imported(): void
    {
        $contents = [
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32” Tv,10,399.99,'
        ];
        $path = $this->createFile(implode("\n", $contents));

        $this->artisan("app:import-products $path");
        $this->assertTrue(Models\Product::where('code', 'P0001')->exists());

        unlink($path);
    }

    /**
     * Import progress should be shown.
     */
    public function test_report(): void
    {
        $contents = [
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32” Tv,10,399.99,'
        ];
        $path = $this->createFile(implode("\n", $contents));

        $this
            ->artisan("app:import-products $path")
            ->expectsOutput('Processed: 1')
            ->expectsOutput('Successful: 1')
            ->expectsOutput('Skipped: 0');

        unlink($path);
    }

    /**
     * Skipped products should be reported.
     */
    public function test_skipped(): void
    {
        $contents = [
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32” Tv,9,1,'
        ];
        $path = $this->createFile(implode("\n", $contents));

        $this
            ->artisan("app:import-products $path")
            ->expectsOutput('Skipped: 1 (P0001)');

        unlink($path);
    }

    /**
     * Malformed products should be reported.
     */
    public function test_malformed(): void
    {
        $contents = [
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'Not a product.'
        ];
        $path = $this->createFile(implode("\n", $contents));

        $this
            ->artisan("app:import-products $path")
            ->expectsOutput('Row 2 is malformed.');

        unlink($path);
    }

    /**
     * When used with --test the command should provide a report, but do no DB changes.
     */
    public function test_test_run(): void
    {
        $contents = [
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued',
            'P0001,TV,32” Tv,10,399.99,'
        ];
        $path = $this->createFile(implode("\n", $contents));

        $this
            ->artisan("app:import-products --test $path")
            ->expectsOutput('Processed: 1')
            ->expectsOutput('Successful: 1')
            ->expectsOutput('Skipped: 0');
        $this->assertFalse(Models\Product::where('code', 'P0001')->exists());

        unlink($path);
    }

    private function createFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'products');
        $file = fopen($path, 'w');
        fwrite($file, $contents);
        fclose($file);
        return $path;
    }
}
