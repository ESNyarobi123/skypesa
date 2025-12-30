<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAppVersionController extends Controller
{
    public function index()
    {
        $versions = AppVersion::latest()->paginate(10);
        return view('admin.app-versions.index', compact('versions'));
    }

    public function create()
    {
        return view('admin.app-versions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'version_code' => 'required|string',
            'version_name' => 'required|string',
            'apk_file' => 'nullable|file|mimes:apk,zip|max:102400', // 100MB max
            'manual_file_name' => 'nullable|string',
            'description' => 'nullable|string',
            'features' => 'nullable|string', // Comma separated or new line separated
            'screenshots.*' => 'nullable|image|max:2048',
        ]);

        if (!$request->hasFile('apk_file') && !$request->manual_file_name) {
            return back()->withErrors(['apk_file' => 'Please upload an APK file or provide a manual filename.'])->withInput();
        }

        $data = $request->only(['version_code', 'version_name', 'description', 'is_active', 'force_update']);
        $data['is_active'] = $request->has('is_active');
        $data['force_update'] = $request->has('force_update');

        // Handle APK Upload or Manual File
        if ($request->hasFile('apk_file')) {
            $path = $request->file('apk_file')->store('apks', 'public');
            $data['apk_path'] = $path;
        } elseif ($request->manual_file_name) {
            $filename = $request->manual_file_name;
            // Ensure the file exists in storage/app/public/apks
            // We assume user put it in public/storage/apks/ which maps to storage/app/public/apks/
            $path = 'apks/' . $filename;
            
            if (!Storage::disk('public')->exists($path)) {
                // Try checking if they just put it in root of public/storage
                if (Storage::disk('public')->exists($filename)) {
                    $path = $filename;
                } else {
                     return back()->withErrors(['manual_file_name' => "File '$filename' not found in public/storage/apks/ or public/storage/"])->withInput();
                }
            }
            $data['apk_path'] = $path;
        }

        // Handle Features (split by new line)
        if ($request->features) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $request->features)));
        }

        // Handle Screenshots
        $screenshots = [];
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $screenshots[] = $file->store('app-screenshots', 'public');
            }
        }
        $data['screenshots'] = $screenshots;

        // If this is set to active, deactivate others (optional, depends on logic)
        if ($data['is_active']) {
            AppVersion::where('is_active', true)->update(['is_active' => false]);
        }

        AppVersion::create($data);

        return redirect()->route('admin.app-versions.index')->with('success', 'App version uploaded successfully.');
    }

    public function edit(AppVersion $appVersion)
    {
        return view('admin.app-versions.edit', compact('appVersion'));
    }

    public function update(Request $request, AppVersion $appVersion)
    {
        $request->validate([
            'version_code' => 'required|string',
            'version_name' => 'required|string',
            'apk_file' => 'nullable|file|mimes:apk,zip|max:102400',
            'manual_file_name' => 'nullable|string',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'screenshots.*' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['version_code', 'version_name', 'description']);
        $data['is_active'] = $request->has('is_active');
        $data['force_update'] = $request->has('force_update');

        if ($request->hasFile('apk_file')) {
            // Delete old file
            if ($appVersion->apk_path) {
                Storage::disk('public')->delete($appVersion->apk_path);
            }
            $data['apk_path'] = $request->file('apk_file')->store('apks', 'public');
        } elseif ($request->manual_file_name) {
             $filename = $request->manual_file_name;
             $path = 'apks/' . $filename;
             
             if (!Storage::disk('public')->exists($path)) {
                 if (Storage::disk('public')->exists($filename)) {
                     $path = $filename;
                 } else {
                      return back()->withErrors(['manual_file_name' => "File '$filename' not found in public/storage/apks/ or public/storage/"])->withInput();
                 }
             }
             
             // Delete old file if different
             if ($appVersion->apk_path && $appVersion->apk_path !== $path) {
                 // Don't delete if it's the same file we are manually pointing to (rare case but possible)
                 Storage::disk('public')->delete($appVersion->apk_path);
             }
             $data['apk_path'] = $path;
        }

        if ($request->features) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $request->features)));
        }

        if ($request->hasFile('screenshots')) {
            $screenshots = $appVersion->screenshots ?? [];
            foreach ($request->file('screenshots') as $file) {
                $screenshots[] = $file->store('app-screenshots', 'public');
            }
            $data['screenshots'] = $screenshots;
        }
        
        if ($data['is_active']) {
             AppVersion::where('id', '!=', $appVersion->id)->where('is_active', true)->update(['is_active' => false]);
        }

        $appVersion->update($data);

        return redirect()->route('admin.app-versions.index')->with('success', 'App version updated successfully.');
    }

    public function destroy(AppVersion $appVersion)
    {
        if ($appVersion->apk_path) {
            Storage::disk('public')->delete($appVersion->apk_path);
        }
        // Delete screenshots if needed, but they might be shared or I'm lazy to loop
        
        $appVersion->delete();
        return redirect()->route('admin.app-versions.index')->with('success', 'App version deleted.');
    }
    
    public function toggleStatus(AppVersion $appVersion)
    {
        $newState = !$appVersion->is_active;
        
        if ($newState) {
             AppVersion::where('id', '!=', $appVersion->id)->where('is_active', true)->update(['is_active' => false]);
        }
        
        $appVersion->update(['is_active' => $newState]);
        
        return back()->with('success', 'Status updated.');
    }
}
