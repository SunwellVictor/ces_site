User-agent: *
Allow: /

# Disallow private/admin areas
Disallow: /admin/
Disallow: /account/
Disallow: /dashboard/
Disallow: /profile/
Disallow: /downloads/
Disallow: /download/

# Disallow authentication pages
Disallow: /login
Disallow: /register
Disallow: /password/
Disallow: /email/

# Disallow cart and checkout
Disallow: /cart
Disallow: /checkout/

# Disallow API endpoints
Disallow: /api/
Disallow: /webhooks/

# Allow important SEO files
Allow: /sitemap*.xml
Allow: /robots.txt

# Sitemap location
Sitemap: {{ route('sitemap.index') }}