<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Suburb;
use Illuminate\Support\Facades\File;


class SuburbsSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/suburbs.json');
        $batchSize = 500; // Define your batch size here (e.g., 500, 1000, 2000)
        $seededCount = 0;

        if (!File::exists($filePath)) { // Use File facade for better practice
            $this->command->error("File not found at: $filePath");
            return;
        }

        $json = File::get($filePath); // Use File facade
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("JSON error: " . json_last_error_msg());
            return;
        }

        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            $this->command->error('"data" key not found or is not an array in suburbs.json');
            return;
        }

        $allSuburbs = $decoded['data'];
        $totalSuburbs = count($allSuburbs);

        // Use a progress bar for better UX during seeding
        $progressBar = $this->command->getOutput()->createProgressBar($totalSuburbs);
        $progressBar->start();

        // Process suburbs in chunks
        foreach (array_chunk($allSuburbs, $batchSize) as $batch) {
            $suburbDataToInsert = [];
            foreach ($batch as $item) {
                // Prepare data for mass insertion (more efficient than individual creates)
                $suburbDataToInsert[] = [
                    'name' => $item['suburb'],
                    'postcode' => (string) $item['postcode'],
                    'state' => $item['state'],
                    'country' => 'Australia',
                    'created_at' => now(), // Add timestamps for mass insertion
                    'updated_at' => now(),
                ];
            }

            // Insert the batch
            Suburb::insert($suburbDataToInsert);
            $seededCount += count($batch);
            $progressBar->advance(count($batch));
        }

        $progressBar->finish();
        $this->command->newLine(); // Move to a new line after the progress bar
        $this->command->info("Seeded {$seededCount} suburbs successfully in batches.");
    }
}
