<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BulkProductsImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];
    protected ?string $extractedPath = null;

    public function __construct(?string $extractedPath = null)
    {
        $this->extractedPath = $extractedPath; // temp folder for extracted ZIP images
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // considering heading row
            try {
                if (empty($row['name'])) { continue; }

                // Category
                $categoryId = null;
                if (!empty($row['category'])) {
                    $category = Category::firstOrCreate(['name' => trim($row['category'])]);
                    $categoryId = $category->id;
                }

                // Active flag
                $isActive = true;
                if (isset($row['is_active'])) {
                    $val = strtolower(trim((string)$row['is_active']));
                    $isActive = in_array($val, ['1','true','yes','active'], true);
                }

                // Base price & discount
                $price = $this->numeric($row['base_price'] ?? $row['price'] ?? 0);
                $discount = $this->numeric($row['discount_amount'] ?? null);
                if ($discount !== null && $discount <= 0) { $discount = null; }

                // Create/find product
                $product = Product::firstOrCreate([
                    'name' => $row['name'],
                ], [
                    'description' => $row['description'] ?? null,
                    'category_id' => $categoryId,
                    'is_active' => $isActive,
                    'is_featured' => false,
                ]);

                // Update simple attributes if new info provided
                $product->fill([
                    'description' => $row['description'] ?? $product->description,
                    'category_id' => $categoryId ?? $product->category_id,
                    'is_active' => $isActive,
                ])->save();

                // Variant create (one row = one variant)
                $product->variants()->create([
                    'name' => $row['variant_name'] ?? 'Default',
                    'value' => $row['variant_value'] ?? 'Standard',
                    'unit' => $row['unit'] ?? null,
                    'sku' => $row['sku'] ?? null,
                    'base_price' => $price,
                    'stock_quantity' => $row['stock_quantity'] ?? 0,
                    'discount_amount' => $discount,
                    'weight' => $this->numeric($row['weight'] ?? null),
                    'is_active' => $isActive,
                ]);

                // Handle images
                $this->handleImages($product, $row);
            } catch (\Throwable $e) {
                $this->errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function numeric($value)
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return $value;
        $clean = preg_replace('/[^\d.]/','', (string)$value);
        return $clean === '' ? null : $clean;
    }

    protected function handleImages(Product $product, $row): void
    {
        $mode = strtolower(trim((string)($row['image_mode'] ?? '')));
        if (!$mode) return; // no image handling requested

        $images = [];
        foreach (['image_1','image_2','image_3'] as $key) {
            if (!empty($row[$key])) { $images[] = trim($row[$key]); }
        }
        if (empty($images)) return;

        // Only set primary image if product doesn't have one yet
        if ($product->image_path) return;

        if ($mode === 'url') {
            foreach ($images as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    try {
                        $contents = @file_get_contents($url);
                        if ($contents) {
                            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                            $filename = 'prod_' . Str::random(10) . '.' . $ext;
                            Storage::disk('public')->put('products/' . $filename, $contents);
                            $product->update(['image_path' => 'products/' . $filename]);
                            break;
                        }
                    } catch (\Throwable $e) {
                        $this->errors[] = 'Image download failed for URL ' . $url . ': ' . $e->getMessage();
                    }
                }
            }
        } elseif ($mode === 'filename' && $this->extractedPath && is_dir($this->extractedPath)) {
            foreach ($images as $fileName) {
                $matches = glob($this->extractedPath . DIRECTORY_SEPARATOR . $fileName, GLOB_BRACE);
                if (empty($matches)) {
                    // try case-insensitive search
                    $lower = strtolower($fileName);
                    $matches = array_filter(glob($this->extractedPath . DIRECTORY_SEPARATOR . '*'), function($p) use ($lower){
                        return strtolower(basename($p)) === $lower;
                    });
                }
                if (!empty($matches)) {
                    $source = reset($matches);
                    $ext = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
                    $newName = 'prod_' . Str::random(10) . '.' . $ext;
                    $target = storage_path('app/public/products/' . $newName);
                    if (!is_dir(dirname($target))) { @mkdir(dirname($target), 0775, true); }
                    copy($source, $target);
                    $product->update(['image_path' => 'products/' . $newName]);
                    break;
                }
            }
        }
    }
}
