<?php

namespace App\Http\Controllers;


use App\Models\Service;
use App\Models\Project;
use App\Models\PortfolioSetting;
use App\Models\Skill;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\News;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('id')->get();
        $projects = Project::orderBy('sort_order')->orderByDesc('created_at')->take(3)->get();
        $projectCount = Project::count();

        $cvPath = PortfolioSetting::where('key', 'cv_path')->value('value');
        $profilePhoto = PortfolioSetting::where('key', 'profile_photo')->value('value');
        $skills = Skill::orderBy('type')->orderBy('sort_order')->orderBy('name')->get()->groupBy('type');
        $profile = PortfolioSetting::profileContent();
        $educations = Education::orderBy('sort_order')->get()->filter(fn ($education) => filled($education->institution))->values();
        $news = News::whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at')->take(3)->get();
        $newsCount = News::whereNotNull('published_at')->where('published_at', '<=', now())->count();
        $isPreview = request()->boolean('preview') && session('portfolio_admin') && session()->has('profile_preview');
        if ($isPreview) {
            $profile = array_replace($profile, session('profile_preview'));
        }

        return view('landing', compact('services', 'projects', 'projectCount', 'cvPath', 'profilePhoto', 'skills', 'profile', 'isPreview', 'educations', 'news', 'newsCount'));
    }

    public function portfolioIndex()
    {
        $projects = Project::orderBy('sort_order')->orderByDesc('created_at')->paginate(9)->withQueryString();
        return view('projects.index', compact('projects'));
    }

    public function newsIndex()
    {
        $news = News::whereNotNull('published_at')->where('published_at', '<=', now())
            ->orderByDesc('published_at')->paginate(9)->withQueryString();

        return view('news.index', compact('news'));
    }

    public function showNews(News $news)
    {
        abort_unless($news->published_at && $news->published_at->lte(now()), 404);
        $relatedNews = News::whereNotNull('published_at')->where('published_at', '<=', now())
            ->where('id', '!=', $news->id)->orderByDesc('published_at')->take(3)->get();

        return view('news.show', compact('news', 'relatedNews'));
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:5000',
            'consultation' => 'nullable|boolean',
            'phone' => 'nullable|string|max:30',
            'service' => 'required_if:consultation,1|nullable|string|max:255',
            'budget' => 'nullable|string|max:100',
            'timeline' => 'nullable|string|max:100',
        ]);

        $isConsultation = $request->boolean('consultation');
        if ($isConsultation) {
            $details = [
                '[PERMINTAAN KONSULTASI]',
                'Paket: ' . $data['service'],
                'Kisaran anggaran: ' . ($data['budget'] ?: 'Belum ditentukan'),
                'Target pengerjaan: ' . ($data['timeline'] ?: 'Fleksibel'),
                'WhatsApp peminat: ' . ($data['phone'] ?: 'Tidak dicantumkan'),
                '',
                'Detail kebutuhan:',
                $data['message'],
            ];
            $data['message'] = implode("\n", $details);
        }

        ContactMessage::create(['name' => $data['name'], 'email' => $data['email'], 'message' => $data['message']]);

        if ($isConsultation) {
            $number = PortfolioSetting::where('key', 'admin_whatsapp')->value('value') ?: config('portfolio.whatsapp');
            $number = preg_replace('/\D+/', '', (string) $number);
            if (str_starts_with($number, '0')) $number = '62' . substr($number, 1);
            elseif (str_starts_with($number, '8')) $number = '62' . $number;
            $whatsappUrl = strlen($number) >= 8 && strlen($number) <= 15
                ? 'https://wa.me/' . $number . '?' . http_build_query(['text' => "Halo, saya {$data['name']} ({$data['email']}).\n\n" . $data['message']])
                : null;

            if ($request->expectsJson()) {
                return response()->json(['saved' => true, 'whatsapp_url' => $whatsappUrl]);
            }

            if ($whatsappUrl) return redirect()->away($whatsappUrl);
            return redirect()->to(route('home') . '#kontak')->with('success', 'Permintaan konsultasi tersimpan di dashboard admin. Nomor WhatsApp admin belum diatur.');
        }

        return redirect()->to(route('home') . '#kontak')->with('success', 'Pesan Anda berhasil dikirim. Terima kasih sudah menghubungi saya.');
    }
}
