# SEO Guide

## Overview
This application implements comprehensive SEO features including meta tags, Open Graph, Twitter Cards, JSON-LD structured data, and automated sitemaps. All SEO elements are managed through the `Meta` service and applied via middleware.

## Meta Tags & Social Media

### Meta Service (`app/Services/Meta.php`)
The centralized Meta service handles all SEO meta tags:

```php
// Basic usage in controllers
$meta = app(Meta::class);
$meta->title('Page Title')
     ->description('Page description')
     ->canonical('https://example.com/page')
     ->og(['image' => 'https://example.com/image.jpg'])
     ->twitter(['card' => 'summary_large_image']);
```

### Supported Meta Types
- **Basic SEO**: title, description, canonical URL
- **Open Graph**: title, description, type, url, image
- **Twitter Cards**: card type, title, description, image
- **Article Meta**: published_time, author, section
- **Robots**: noindex, nofollow directives

### Implementation Locations
- **Products**: `resources/views/products/show.blade.php` (lines 190-205)
- **Blog Posts**: `resources/views/blog/show.blade.php` (lines 13-23)
- **Layout**: Meta tags rendered in `@push('head')` sections

## Structured Data (Schema.org)

### Product Schema
**File**: `resources/views/partials/schema/product.blade.php`
**Type**: Product with Offer
**Fields**:
- Product name, description, SKU, URL, category
- Offer with price, currency (JPY), availability
- Brand and seller organization information

### Article Schema
**File**: `resources/views/partials/schema/article.blade.php`
**Type**: Article
**Fields**:
- Headline, description, publication dates
- Author and publisher information
- Main entity page reference

### Usage in Templates
```blade
@push('head')
    @include('partials.schema.product', ['product' => $product])
@endpush
```

## SEO Middleware & Automation

### SeoGuard Middleware
**File**: `app/Http/Middleware/SeoGuard.php`
**Purpose**: Automatically applies noindex to private pages

**Protected Routes**:
- Admin area (`admin.*`)
- Account pages (`account.*`)
- Downloads (`downloads.*`)
- Cart and checkout (`cart.*`, `checkout.*`)
- Authentication pages (`login`, `register`, `password.*`)

### Implementation
```php
// Automatically adds noindex/nofollow to private routes
protected array $privateRoutes = [
    'admin.*', 'account.*', 'dashboard', 'profile.*',
    'downloads.*', 'cart.*', 'checkout.*', 'webhooks.*'
];
```

## Sitemaps

### Sitemap Controller (`app/Http/Controllers/SitemapController.php`)
Generates dynamic XML sitemaps:

1. **Main Index** (`/sitemap.xml`): Links to all sub-sitemaps
2. **Pages** (`/sitemap-pages.xml`): Static pages (home, blog, products)
3. **Posts** (`/sitemap-posts.xml`): Published blog posts
4. **Products** (`/sitemap-products.xml`): Active products

### Sitemap Features
- **Dynamic lastmod**: Based on actual content update times
- **Priority & Changefreq**: Configured per content type
- **Automatic filtering**: Only published/active content included

### Robots.txt
**File**: `resources/views/seo/robots.blade.php`
**Features**:
- Disallows private areas (admin, account, cart, downloads)
- Links to sitemap index
- Generated dynamically at `/robots.txt`

## Google Rich Results Validation

### Testing Tools
1. **Google Rich Results Test**: https://search.google.com/test/rich-results
2. **Schema Markup Validator**: https://validator.schema.org/
3. **Google Search Console**: Monitor rich results performance

### Validation Process
1. **Test Individual Pages**:
   ```bash
   # Test product page
   curl -s "https://yoursite.com/products/product-slug" | grep -A 20 "application/ld+json"
   
   # Test blog post
   curl -s "https://yoursite.com/blog/post-slug" | grep -A 20 "application/ld+json"
   ```

2. **Validate Schema**:
   - Copy JSON-LD from page source
   - Paste into https://validator.schema.org/
   - Check for errors and warnings

3. **Google Rich Results**:
   - Enter page URL in https://search.google.com/test/rich-results
   - Verify Product/Article rich results are detected
   - Check for any validation errors

### Common Rich Results
- **Products**: Price, availability, reviews (when implemented)
- **Articles**: Author, publish date, headline
- **Organization**: Business information (when implemented)

## SEO Configuration

### Environment Variables
```env
APP_NAME="Your Site Name"
APP_URL="https://yoursite.com"
```

### Product SEO Fields
- `seo_title`: Custom title (falls back to product title)
- `seo_description`: Custom description (falls back to product description)

### Blog Post SEO Fields
- `seo_title`: Custom title (falls back to post title)
- `seo_description`: Custom description (falls back to excerpt)

## Testing & Validation

### Automated Tests
**File**: `tests/Feature/SeoTest.php`
- Meta tag presence validation
- JSON-LD schema validation
- Sitemap generation tests
- Robots.txt content verification

### Manual Testing Checklist
1. **Meta Tags**: View page source, check `<head>` section
2. **Open Graph**: Test with Facebook Debugger
3. **Twitter Cards**: Test with Twitter Card Validator
4. **Schema**: Validate with Google Rich Results Test
5. **Sitemaps**: Check `/sitemap.xml` loads and contains expected URLs

### Performance Monitoring
- **Google Search Console**: Monitor indexing and rich results
- **Core Web Vitals**: Track page speed impact of SEO elements
- **Crawl Errors**: Monitor for sitemap and robots.txt issues

## Best Practices

### Content Optimization
- Keep titles under 60 characters
- Keep descriptions between 150-160 characters
- Use descriptive, keyword-rich URLs
- Implement proper heading hierarchy (H1, H2, H3)

### Technical SEO
- Ensure all pages have unique titles and descriptions
- Use canonical URLs to prevent duplicate content
- Implement proper 301 redirects for changed URLs
- Monitor and fix broken internal links

### Schema.org Guidelines
- Use specific schema types (Product, Article vs generic Thing)
- Include all required properties for rich results
- Test schema changes before deployment
- Keep structured data synchronized with visible content