<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use Illuminate\Http\Request;

class ExtensionController extends Controller
{
    public function index()
    {
        $this->syncExtensions();
        $extensions = Extension::all();

        return view('settings.extensions', compact('extensions'));
    }

    protected function syncExtensions()
    {
        $availableExtensions = config('extensions', []);

        foreach ($availableExtensions as $slug => $data) {
            Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image' => $data['image'],
                    'help_text' => $data['help_text'],
                    // Only set default shortcode if it doesn't exist to preserve existing config
                    'shortcode' => Extension::where('slug', $slug)->exists()
                        ? Extension::where('slug', $slug)->first()->shortcode
                        : $data['shortcode'],
                ]
            );
        }
    }

    public function update(Request $request, Extension $extension)
    {
        $shortcode = $extension->shortcode;
        foreach ($shortcode as $key => $value) {
            if ($request->has($key)) {
                $shortcode[$key] = $request->input($key);
            }
        }

        $extension->update([
            'shortcode' => $shortcode,
        ]);

        return back()->with('success', $extension->name.' updated successfully.');
    }

    public function toggleStatus(Extension $extension)
    {
        $extension->update([
            'status' => ! $extension->status,
        ]);

        $status = $extension->status ? 'Enabled' : 'Disabled';

        return back()->with('success', $extension->name.' '.$status.' successfully.');
    }
}
