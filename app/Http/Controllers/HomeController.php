<?php

namespace App\Http\Controllers;


use App\Models\Service;
use App\Models\Project;
use App\Models\PortfolioSetting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('id')->get();
        $projects = Project::latest()->paginate(4)->fragment('karya');

        $cvPath = PortfolioSetting::where('key', 'cv_path')->value('value');

        return view('landing', compact('services', 'projects', 'cvPath'));
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $phone = config('portfolio.whatsapp');
        if (!$phone) {
            return back()->withErrors(['message' => 'Kontak WhatsApp belum disiapkan. Silakan hubungi saya lewat media sosial.'])->withInput();
        }

        $text = "Halo Nadhim Alim, saya " . urlencode($request->name) . " (" . urlencode($request->email) . ").%0A%0A" . urlencode($request->message);

        return redirect()->away("https://wa.me/{$phone}?text={$text}");
    }
}
