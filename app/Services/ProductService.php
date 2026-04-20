<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    /**
     * @return Collection<int, array{id:int,name:string}>
     */
    public function listSelectableCategories(): Collection
    {
        return $this->repository->listSelectableCategories();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Product
    {
        $normalized = $this->normalizePayload($payload);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug']);

        return DB::transaction(fn (): Product => $this->repository->create($normalized));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Product $product, array $payload): Product
    {
        $normalized = $this->normalizePayload($payload, $product);
        $normalized['slug'] = $this->resolveUniqueSlug((string) $normalized['slug'], $product->id);

        return DB::transaction(fn (): Product => $this->repository->update($product, $normalized));
    }

    public function delete(Product $product): void
    {
        $this->repository->delete($product);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload, ?Product $product = null): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $slugInput = trim((string) ($payload['slug'] ?? ''));

        if ($slugInput === '') {
            $slugInput = Str::slug($name);
        }

        if ($slugInput === '') {
            $slugInput = $product?->slug ?? 'product';
        }

        return [
            'category_id' => (int) ($payload['category_id'] ?? 0),
            'name' => $name,
            'slug' => $slugInput,
            'short_description' => $this->normalizeNullableString($payload['short_description'] ?? null),
            'description' => $this->normalizeNullableString($payload['description'] ?? null),
            'price' => number_format((float) ($payload['price'] ?? 0), 2, '.', ''),
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'is_featured' => (bool) ($payload['is_featured'] ?? false),
            'stock_status' => $this->normalizeStockStatus((string) ($payload['stock_status'] ?? Product::STOCK_IN_STOCK)),
            'image_path' => $this->normalizeNullableString($payload['image_path'] ?? null),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! \is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeStockStatus(string $stockStatus): string
    {
        if (! \in_array($stockStatus, Product::stockStatuses(), true)) {
            return Product::STOCK_IN_STOCK;
        }

        return $stockStatus;
    }

    private function resolveUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($baseSlug);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'product';

        $slug = $baseSlug;
        $counter = 2;

        while ($this->repository->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
