@props([
    'page' => null,
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'image' => null,
    'type' => 'website',
    'keywords' => null,
    'jsonLd' => null,
    'faqs' => [],
    'breadcrumbs' => null,
    'schema' => null,
])

<x-seo.head
    :page="$page"
    :title="$title"
    :description="$description"
    :canonical="$canonical"
    :robots="$robots"
    :image="$image"
    :type="$type"
    :keywords="$keywords"
    :json-ld="$jsonLd"
    :faqs="$faqs"
    :breadcrumbs="$breadcrumbs"
    :schema="$schema"
/>
