<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- Metaها از متدهای کامپوننت -->
@if(isset($this))
    <meta name="description" content="{{ $this->metaDescription() }}">
    <meta name="keywords" content="{{ $this->metaKeywords() }}">
@else
    <meta name="description" content="توضیحات پیش‌فرض">
    <meta name="keywords" content="">
@endif

<title>{{ $title ?? config('app.name') }}</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
