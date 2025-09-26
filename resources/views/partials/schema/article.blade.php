{{-- Article JSON-LD Schema for Blog Posts --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $post->title }}",
    "description": "{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 160) }}",
    "datePublished": "{{ $post->published_at->toISOString() }}",
    "dateModified": "{{ $post->updated_at->toISOString() }}",
    "author": {
        "@type": "Person",
        "name": "{{ $post->author->name ?? $post->user->name }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ config('app.url') }}"
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ route('blog.show', $post->slug) }}"
    },
    "url": "{{ route('blog.show', $post->slug) }}"
}
</script>