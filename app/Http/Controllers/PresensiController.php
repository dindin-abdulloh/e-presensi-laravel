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
        return view('presensi.create');
    }


    public function store(Request $request)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $tgl_presensi = Carbon::now(); // Use Carbon
        $lokasi = $request->lokasi;
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

        $presensi = new Presensi(); // Create a new instance of the Eloquent model
        $presensi->fill($data); // Fill the model attributes
        $save = $presensi->save(); // Save the model to the database

        if ($save) {
            // Use a proper response method
            echo 0;
            Storage::put($file, $image_base64);
        } else {
            // Use a proper response method
           echo 1;
        }

    }
}
