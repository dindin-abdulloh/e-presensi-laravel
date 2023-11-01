@extends('layouts.presensi')
<style>
    .web-cam-capture, .web-cam-capture video{
        display: inline-block;
        width: 100% !important;
        margin: auto;
        height: auto !important;
        border-radius: 15px;

    }

    #map { height: 220px; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@section('header')

<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="#" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">E-Presensi</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
    <div class="row" style="margin-top: 70px">
        <div class="col">
            <input type="hidden" id="lokasi">
            <div class="web-cam-capture">
            </div>
        </div>
    </div>
    <div class="col mt-2">
        <div class="row">
            <button id="takeabsen" class="btn btn-primary btn-block"><ion-icon name="camera-outline"></ion-icon> Absen Masuk</button>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>
@endsection


@push('presensiScript')
    <script>
        Webcam.set({
            height: 480,
            width: 640,
            image_format: 'jpeg',
            jpeg_quality: 80
        })

        Webcam.attach('.web-cam-capture')

        var lokasi = document.getElementById('lokasi');
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        function successCallback(position){
            lokasi.value = position.coords.latitude +","+ position.coords.longitude;
            var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 15);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);

            var circle = L.circle([-7.026058565149574, 107.68312991750234], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 130
            }).addTo(map);
        }

        function errorCallback(){

        }

        $("#takeabsen").click(function(e){
            Webcam.snap(function(uri){
                 image = uri;
            })

           var lokasi = $("#lokasi").val();
           $.ajax({
            type: 'POST',
            url: '/presensi/store',
            data: {
                _token: "{{csrf_token()}}",
                image: image,
                lokasi: lokasi
            },
            cache: false,
            success: function(res){
                alert(res)
            }
           })
        })
    </script>
@endpush
