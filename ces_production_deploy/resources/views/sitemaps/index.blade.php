@php
header('Content-Type: application/xml; charset=utf-8');
$xmlDeclaration = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
echo $xmlDeclaration . "\n";
@endphp
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($sitemaps as $sitemap)
    <sitemap>
        <loc>{{ $sitemap['loc'] }}</loc>
        <lastmod>{{ $sitemap['lastmod'] }}</lastmod>
    </sitemap>
@endforeach
</sitemapindex>