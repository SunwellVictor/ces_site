# SEO Implementation Guide

## Overview

The application includes a comprehensive SEO system with dynamic meta tags, Open Graph support, Twitter Cards, and structured data. All SEO functionality is managed through the `Meta` service and Blade partials.

## Meta Service (`App\Services\Meta`)

### Basic Usage
```php
// In your controller
public function show(Post $post, Meta $meta)
{
    $meta->title($post->seo_title ?: $post->title)
         ->description($post->seo_description ?: $post->excerpt)
         ->canonical(route('blog.show', $post->slug));
    
    return view('blog.show', compact('post'));
}
```

### Available Methods
- `title(string $title)` - Set page title
- `description(string $description)` - Set meta description
- `canonical(string $url)` - Set canonical URL
- `og(array $data)` - Set Open Graph tags
- `twitter(array $data)` - Set Twitter Card tags
- `article(array $data)` - Set article-specific tags
- `noindex(bool $noindex)` - Set noindex flag
- `setRobots(string $robots)` - Set robots meta tag

## Meta Tags Partial

### Implementation
Include in your layout's `<head>` section:
```blade
@include('partials.meta')
```

### Generated Tags
The partial automatically generates:
- Basic meta tags (title, description, canonical)
- Robots meta tags (noindex, custom robots)
- Open Graph tags for social sharing
- Twitter Card tags
- Article-specific tags for blog posts

## Open Graph Implementation

### Basic Setup
```php
$meta->og([
    'type' => 'website',
    'title' => 'Page Title',
    'description' => 'Page description',
    'url' => 'https://example.com/page',
    'image' => 'https://example.com/image.jpg',
]);
```

### Article Pages
```php
$meta->og([
    'type' => 'article',
    'title' => $post->title,
    'description' => $post->excerpt,
    'url' => route('blog.show', $post->slug),
])
->article([
    'published_time' => $post->published_at->toISOString(),
    'author' => $post->author->name,
]);
```

## Twitter Cards

### Summary Card
```php
$meta->twitter([
    'card' => 'summary',
    'title' => 'Page Title',
    'description' => 'Page description',
]);
```

### Large Image Card
```php
$meta->twitter([
    'card' => 'summary_large_image',
    'title' => 'Article Title',
    'description' => 'Article excerpt',
    'image' => 'https://example.com/featured-image.jpg',
]);
```

## Robots.txt Management

### Dynamic Robots.txt
The system generates robots.txt dynamically via `SeoController@robots`:

```
User-agent: *
Disallow: /admin/
Disallow: /cart
Disallow: /checkout
Disallow: /account
Disallow: /download

Sitemap: https://yourdomain.com/sitemap.xml
```

### Protected Areas
- `/admin/` - Admin panel
- `/cart` - Shopping cart
- `/checkout` - Checkout process
- `/account` - User account area
- `/download` - Download area

## Noindex Implementation

### Automatic Noindex
Private pages automatically get noindex tags:
- Account dashboard
- Download pages
- Admin areas
- Cart and checkout

### Manual Noindex
```php
$meta->noindex(true);
// Generates: <meta name="robots" content="noindex, nofollow">
```

## SEO Fields in Models

### Blog Posts
Posts include dedicated SEO fields:
- `seo_title` - Custom title for search engines
- `seo_description` - Custom meta description

### Usage Pattern
```php
// Use SEO fields with fallbacks
$title = $post->seo_title ?: $post->title;
$description = $post->seo_description ?: $post->excerpt;
```

## Google Validation

### Testing Tools
1. **Google Search Console**
   - Submit sitemap.xml
   - Monitor crawl errors
   - Check mobile usability

2. **Rich Results Test**
   - Test structured data: https://search.google.com/test/rich-results
   - Validate article markup
   - Check Open Graph tags

3. **PageSpeed Insights**
   - Test Core Web Vitals
   - Monitor performance scores
   - Check mobile optimization

### Validation Checklist
- [ ] Title tags under 60 characters
- [ ] Meta descriptions 150-160 characters
- [ ] Canonical URLs properly set
- [ ] Open Graph tags complete
- [ ] Twitter Cards configured
- [ ] Robots.txt accessible
- [ ] Sitemap.xml submitted
- [ ] No duplicate content issues

## Schema Markup (Future Enhancement)

### Recommended Implementation
```php
// In Meta service, add schema method
public function schema(array $data): self
{
    $this->data['schema'] = $data;
    return $this;
}

// Usage for articles
$meta->schema([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post->title,
    'author' => [
        '@type' => 'Person',
        'name' => $post->author->name,
    ],
    'datePublished' => $post->published_at->toISOString(),
]);
```

## Performance Considerations

### Meta Service Optimization
- Service is singleton, reused across requests
- Minimal memory footprint
- Lazy evaluation of meta data

### Caching Strategy
- Static pages: Cache meta tags in view cache
- Dynamic pages: Cache at controller level
- Use Laravel's cache tags for invalidation

## Common SEO Issues

### Duplicate Content
- Ensure canonical URLs are set
- Use consistent URL structure
- Implement proper redirects

### Missing Meta Tags
- Always set title and description
- Provide fallbacks for dynamic content
- Use default values for empty fields

### Social Sharing
- Test Open Graph tags with Facebook Debugger
- Validate Twitter Cards with Card Validator
- Ensure images are properly sized

## Monitoring and Maintenance

### Regular Checks
- Monitor Google Search Console weekly
- Check for crawl errors monthly
- Update meta descriptions for low CTR pages
- Review and update robots.txt as needed

### Analytics Integration
- Track organic search traffic
- Monitor keyword rankings
- Analyze user engagement metrics
- A/B test meta descriptions