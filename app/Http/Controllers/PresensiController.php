<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Presensi;
use Carbon\Carbon;


class PresensiController extends Controller
{
    //
    public function create()
    {
        $hariIni = Carbon::now();
        $nik = Auth::guard('karyawan')->user()->nik;

        $cek = Presensi::select()->where('tgl_presensi', $hariIni)->where('nik', $nik)->count();
        return view('presensi.create', compact('cek'));
    }


    public function store(Request $request)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $tgl_presensi = Carbon::now();
        $lat_kantor = -6.933111;
        $long_kantor = 107.623460;
        $lokasi = $request->lokasi;
        // dd($lokasi);
        $lokasi_user = explode(",", $lokasi);
        $long_user = $lokasi_user[0];
        $lat_user = $lokasi_user[1];
        $jarak = $this->distance($long_kantor, $lat_kantor, $lat_user, $long_user);
        $radius = round($jarak['meters']);

        $image = $request->image;
        $folderPath = "public/uploads/absensi";
        $formatName = $nik . "-" . $tgl_presensi->format('Y-m-d_H-i-s');
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;

        $data = [
            'nik' => $nik,
            'tgl_presensi' => $tgl_presensi,
            'jam_in' => $tgl_presensi->format('H:i:s'),
            'foto_in' => $fileName,
            'lokasi_in' => $lokasi,
        ];

        $presensi = new Presensi();
        $existingRecord = $presensi->where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->first();
        if($radius > 100){
            return response()->json(['message' => 'Maaf, anda berada diluar radius!', 'status' => 400,]);
        }else{
            if ($existingRecord) {
                // Update the existing record
                $existingRecord->update([
                    'jam_out' => $tgl_presensi->format('H:i:s'),
                    'foto_out' => $fileName,
                    'lokasi_out' => $lokasi,
                ]);

                // Use a proper response method
                return response()->json(['message' => 'Terimakasih, Selamat Jalan!', 'status' => 200, 'tipe' => 'out']);
            } else {
                // Create a new record
                $presensi->fill($data);
                $presensi->save();

                if ($presensi->wasRecentlyCreated) {
                    // Use a proper response method
                    Storage::put($file, $image_base64);
                    return response()->json(['message' => "Terimakasih, Selamat Bekerja!", 'status' => 200, 'tipe' => 'in']);
                } else {
                    // Use a proper response method
                    return response()->json(['message' => "Data Gagal Disimpan"], 400);
                }
            }
        }


    }

    // Menghitung jarak titik koordinat
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }
}
