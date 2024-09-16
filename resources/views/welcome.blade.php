<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="app"></div>
    {{-- <div id="map"></div> --}}
</body>
{{-- <script src="{{ mix('js/app.js') }}"></script> --}}
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>


</html>
