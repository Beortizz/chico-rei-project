<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * O campo image guarda um caminho relativo ao disco (upload) ou uma URL absoluta (seeder e importação).
 */
class ProductService
{
    private const string IMAGE_DISK = 'public';

    private const string IMAGE_DIRECTORY = 'products';

    private const int IMAGE_MAX_SIDE = 1200;

    private const int IMAGE_QUALITY = 80;

    public function create(array $data): Product
    {
        $data = $this->storeUploadedImage($data);

        try {
            return Product::create($data);
        } catch (Throwable $e) {
            $this->deleteImage($data['image'] ?? null);

            throw $e;
        }
    }

    /**
     * Substitui a imagem quando um novo arquivo é enviado ou quando remove_image é verdadeiro,
     * e apaga o arquivo anterior só depois de o banco ser atualizado.
     */
    public function update(Product $product, array $data): Product
    {
        $previousImage = $product->image;
        $removeImage = (bool) Arr::pull($data, 'remove_image', false);

        $data = $this->storeUploadedImage($data);

        if ($removeImage && ! array_key_exists('image', $data)) {
            $data['image'] = null;
        }

        try {
            $product->update($data);
        } catch (Throwable $e) {
            if ($data['image'] ?? null) {
                $this->deleteImage($data['image']);
            }

            throw $e;
        }

        if ($product->wasChanged('image')) {
            $this->deleteImage($previousImage);
        }

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();

        $this->deleteImage($product->image);
    }

    public function imageUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        return $this->isExternalImage($path) ? $path : Storage::disk(self::IMAGE_DISK)->url($path);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function storeUploadedImage(array $data): array
    {
        if (($data['image'] ?? null) instanceof UploadedFile) {
            $data['image'] = $this->storeOptimizedImage($data['image']);
        }

        return $data;
    }

    /**
     * Reduz o maior lado a IMAGE_MAX_SIDE (sem ampliar) e converte para WebP.
     * Se o GD não conseguir decodificar o arquivo, guarda o original.
     */
    private function storeOptimizedImage(UploadedFile $file): string
    {
        $source = imagecreatefromstring($file->getContent());

        if ($source === false) {
            return Storage::disk(self::IMAGE_DISK)->putFile(self::IMAGE_DIRECTORY, $file);
        }

        imagepalettetotruecolor($source);

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::IMAGE_MAX_SIDE / max($width, $height));

        $image = $scale < 1
            ? imagescale($source, (int) round($width * $scale), (int) round($height * $scale))
            : $source;

        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::IMAGE_QUALITY);
        $webp = ob_get_clean();

        $path = self::IMAGE_DIRECTORY.'/'.Str::random(40).'.webp';
        Storage::disk(self::IMAGE_DISK)->put($path, $webp);

        return $path;
    }

    /**
     * Ignora valores nulos e URLs externas, que não pertencem ao nosso disco.
     */
    private function deleteImage(?string $path): void
    {
        if ($path === null || $this->isExternalImage($path)) {
            return;
        }

        Storage::disk(self::IMAGE_DISK)->delete($path);
    }

    private function isExternalImage(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
