<?php

namespace App\Http\Controllers;

use App\Models\Cryptography;
use Crypt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CryptographyController extends Controller
{
    public function upload(Request $request): string
    {
        $fileNameWithExt = $request?->file('file')?->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
        $extension = $request?->file('file')?->getClientOriginalExtension();
        $fileNameSave = $fileName . '_' . time() . '.' . $extension;
        $path = $request?->file('file')?->storeAs('public/file', $fileNameSave);
        return $fileNameSave;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $files = Cryptography::all();
        return view('master.cryptography.index', compact("files"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.cryptography.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->hasFile('file')) {
            $fileName = $this->upload($request);
            Cryptography::create([
                "name" => $request->name,
                "file" => $fileName
            ]);

            return to_route('cryptography.index');
        }

        return to_route('cryptography.create');
    }

    public function download(Request $request)
    {
        $cryptographyId = intval($request->cryptography);
        $getFile = Cryptography::findOrFail($cryptographyId);

        // Assuming the file is stored under 'storage/app/private/public/file/'
        $filePath = 'public/file/' . $getFile['file'];

        // Check if the file exists in the 'local' disk (storage/app/private)
        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Return the file download response
        return Storage::disk('local')->download($filePath);
    }

    public function show(Cryptography $cryptography)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cryptography $cryptography)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cryptography $cryptography)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cryptography $cryptography): RedirectResponse
    {
        // Assuming the file is stored under 'storage/app/private/public/file/'
        $filePath = 'public/file/' . $cryptography->file;

        // Check if the file exists in the 'local' disk (storage/app/private)
        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found.');
        }

        Storage::delete($filePath);
        $cryptography->delete();

        return to_route('cryptography.index');
    }

    public function encryptFile(Cryptography $cryptography)
    {
        if($cryptography->status == 'LOCKED'){
            return to_route('cryptography.index');
        }
        // Assuming the file is stored under 'storage/app/private/public/file/'
        $filePath = 'public/file/' . $cryptography->file;
        $fileContents = Storage::get($filePath);
        $encryptedContent = Crypt::encryptString($fileContents);
        Storage::put($filePath . '.enc', $encryptedContent);


        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found.');
        }

        Storage::delete($filePath);
        $cryptography->update([
            "file" => $cryptography->file.'.enc',
            "status" => 'LOCKED'
        ]);
        return to_route('cryptography.index');
    }

    public function decryptFile(Cryptography $cryptography)
    {
        if($cryptography->status == 'UNLOCKED'){
            return to_route('cryptography.index');
        }
        $filePath = 'public/file/' . $cryptography->file;
        $fileContents = Storage::get($filePath);
        $decryptedContent = Crypt::decryptString($fileContents);
        $filePath = str_replace('.enc', '', $filePath);
        // dd($filePath, $fileContents, $decryptedContent);
        Storage::put($filePath, $decryptedContent);
        // Storage::delete($filePath);
        $cryptography->update([
            "file" => str_replace('public/file/', '',$filePath),
            "status" => 'UNLOCKED'
        ]);

        return to_route('cryptography.index');
    }
}
