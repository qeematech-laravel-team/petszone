<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Product;

class ImportProductImagesFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:product-images-from-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     $path = storage_path('app/products.xlsx');
    
    //     $rows = Excel::toArray([], $path)[0];
    
    //     foreach ($rows as $index => $row) {
    //         \Log::info($row);
    //         if ($index === 0) continue;
    
    //         $id = $row[0] ?? null;
    //         $slug = $row[1] ?? null;
    //         $url = $row[2] ?? null;
    
            
    //         if (!$id || !$url) continue;
    
            
    //         if (str_starts_with(trim($url), '=')) {
    //             $this->warn("Skipped formula row ID: {$id}");
    //             continue;
    //         }
    
            
    //         $url = trim(urldecode($url));
    
            
    //         if (!filter_var($url, FILTER_VALIDATE_URL)) {
    //             $this->warn("Invalid URL ID: {$id} => {$url}");
    //             continue;
    //         }
    
    //         try {
    
    //             $response = Http::timeout(30)->get($url);
    
    //             if ($response->failed()) {
    //                 $this->error("Failed download ID: {$id}");
    //                 continue;
    //             }
    
    //             $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    //             $extension = $extension ?: 'jpg';
    
    //             $fileName = 'products/' . uniqid() . '.' . $extension;
    
    //             Storage::disk('public')->put($fileName, $response->body());
    
    //             $product = Product::find($id);
    
    //             if (!$product && $slug) {
    //                 $product = Product::where('slug', $slug)->first();
    //             }
    
    //             if (!$product) {
    //                 $this->error("Product not found: ID={$id} | slug={$slug}");
    //                 continue;
    //             }
    
    //             $product->image = $fileName;
    //             $product->save();
    
    //             $this->info("Updated: {$product->id} - {$product->slug}");
    
    //         } catch (\Exception $e) {
    //             $this->error("Error ID {$id}: " . $e->getMessage());
    //         }
    //     }
    // }
    public function handle()
{
    $path = storage_path('app/products.xlsx');

    $rows = Excel::toArray([], $path)[0];

    foreach ($rows as $index => $row) {

        if ($index === 0) continue;

        $id   = $row[0] ?? null;
        $slug = $row[1] ?? null;
        $url  = $row[2] ?? null;

        \Log::info($row);

        if (!$id || !$url) {
            $this->warn("Missing data at row index {$index}");
            continue;
        }

        $url = trim($url);

        // ❌ skip Excel formulas
        if (str_starts_with($url, '=')) {
            $this->warn("Skipped formula ID: {$id}");
            continue;
        }

        // ❌ basic sanity check
        if (!is_string($url)) {
            $this->warn("Invalid type URL ID: {$id}");
            continue;
        }

        // 🔧 fix Arabic / special chars in URL
        $url = $this->sanitizeUrl($url);

        // ❌ final validation (soft)
        if (!str_starts_with($url, 'http')) {
            $this->warn("Invalid URL ID: {$id} => {$url}");
            continue;
        }

        try {

            $response = Http::timeout(30)
                ->retry(2, 500)
                ->get($url);

            if ($response->failed()) {
                $this->error("Failed download ID: {$id}");
                continue;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $extension = $extension ?: 'jpg';

            $fileName = 'products/' . uniqid() . '.' . $extension;

            Storage::disk('public')->put($fileName, $response->body());

            $product = Product::find($id);

            if (!$product && $slug) {
                $product = Product::where('slug', $slug)->first();
            }

            if (!$product) {
                $this->error("Product not found: ID={$id} | slug={$slug}");
                continue;
            }

            $product->thumbnail = $fileName;
            $product->save();

            $this->info("Updated: {$product->id} - {$product->slug}");

        } catch (\Exception $e) {
            $this->error("Error ID {$id}: " . $e->getMessage());
        }
    }
}
private function sanitizeUrl($url)
{
    $url = urldecode($url);

    $parts = parse_url($url);

    if (!isset($parts['scheme']) || !isset($parts['host'])) {
        return $url;
    }

    $path = $parts['path'] ?? '';

    // encode Arabic/special characters safely
    $path = implode('/', array_map('rawurlencode', explode('/', $path)));

    return $parts['scheme'] . '://' . $parts['host'] . $path;
}

}
