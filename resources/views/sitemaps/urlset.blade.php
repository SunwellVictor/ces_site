@php
header('Content-Type: application/xml; charset=utf-8');
$xmlDeclaration = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
echo $xmlDeclaration . "\n";
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        <changefreq>{{ $url['changefreq'] ?? 'monthly' }}</changefreq>
        <priority>{{ $url['priority'] ?? '0.5' }}</priority>
    </url>
@endforeach
</urlset>