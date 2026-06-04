<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\AmsForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AmsFormController extends Controller
{
    /**
     * Display list of AMS forms with search
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $query = AmsForm::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('descriptions', 'like', "%{$search}%");
            });
        }

        $forms = $query
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        return view('assetModule.amsForms.index', compact('forms'));
    }

    /**
     * Store new document
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'file'         => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar',
        ]);

        $path = $request->file('file')->store('ams/forms', 'public');

        AmsForm::create([
            'name'         => $request->name,
            'descriptions' => $request->descriptions,
            'filepath'     => $path,
        ]);

        return redirect()
            ->route('amsForms.index')
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Update document details (and optional file)
     */
    public function update(Request $request, AmsForm $amsForm)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'file'         => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar',
        ]);

        $data = $request->only(['name', 'descriptions']);

        // Replace file if uploaded
        if ($request->hasFile('file')) {
            if ($amsForm->filepath && Storage::disk('public')->exists($amsForm->filepath)) {
                Storage::disk('public')->delete($amsForm->filepath);
            }

            $data['filepath'] = $request->file('file')->store('ams/forms', 'public');
        }

        $amsForm->update($data);

        return redirect()
            ->route('amsForms.index')
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Download document
     */
    public function download(AmsForm $amsForm)
    {
        if (!$amsForm->fileExists()) {
            return redirect()
                ->route('amsForms.index')
                ->with('error', 'File not found.');
        }

        $disk = Storage::disk('public');
        /** @disregard P1013 */
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        return $disk->download($amsForm->filepath, $amsForm->original_filename);
    }

    /**
     * Delete document
     */
    public function destroy(AmsForm $amsForm)
    {
        $amsForm->delete();

        return redirect()
            ->route('amsForms.index')
            ->with('success', 'Document deleted successfully.');
    }
}
