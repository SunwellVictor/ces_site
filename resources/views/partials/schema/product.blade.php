{{-- Product JSON-LD Schema for Products --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ $product->title }}",
    "description": "{{ $product->description }}",
    "sku": "{{ $product->id }}",
    "url": "{{ route('products.show', $product->slug) }}",
    "category": "{{ $product->is_digital ? 'Digital Product' : 'Physical Product' }}",
    "offers": {
        "@type": "Offer",
        "url": "{{ route('products.show', $product->slug) }}",
        "priceCurrency": "JPY",
        "price": "{{ $product->price_cents / 100 }}",
        "availability": "{{ $product->is_active ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "seller": {
            "@type": "Organization",
            "name": "{{ config('app.name') }}",
            "url": "{{ config('app.url') }}"
        }
    },
    "brand": {
        "@type": "Organization",
        "name": "{{ config('app.name') }}"
    }
}
</script>