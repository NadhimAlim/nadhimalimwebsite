<?php

namespace App\Http\Controllers;

use App\Models\PortfolioSetting;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function login()
    {
        if (session('portfolio_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $password = config('portfolio.admin_password');

        if (!$password || !hash_equals($password, $request->password)) {
            return back()->withErrors(['password' => 'Kata sandi tidak cocok.'])->onlyInput();
        }

        $request->session()->regenerate();
        $request->session()->put('portfolio_admin', true);

        return redirect()->route('admin.dashboard');
    }

    public function dashboard()
    {
        if (Service::count() === 0) {
            foreach ([
                ['title' => 'Website Landing Page', 'icon' => 'bi-window', 'description' => 'Website satu halaman yang cepat, responsif, dan dirancang untuk memperkenalkan bisnis atau kampanye Anda.', 'starting_price' => 1500000],
                ['title' => 'Website Company Profile', 'icon' => 'bi-building', 'description' => 'Website profesional untuk memperkuat kepercayaan pelanggan dan menampilkan informasi bisnis Anda.', 'starting_price' => 2500000],
                ['title' => 'Aplikasi Web Custom', 'icon' => 'bi-code-square', 'description' => 'Aplikasi web dengan fitur dan alur kerja yang disesuaikan dengan kebutuhan operasional Anda.', 'starting_price' => 5000000],
            ] as $package) {
                Service::create($package);
            }
        }

        return view('admin.dashboard', [
            'projects' => Project::latest()->get(),
            'services' => Service::orderBy('id')->get(),
            'cvPath' => PortfolioSetting::where('key', 'cv_path')->value('value'),
        ]);
    }

    public function storeService(Request $request)
    {
        Service::create($this->validatedService($request));

        return back()->with('success', 'Paket jasa berhasil ditambahkan.');
    }

    public function updateService(Request $request, Service $service)
    {
        $service->update($this->validatedService($request));

        return back()->with('success', 'Paket jasa berhasil diperbarui.');
    }

    public function deleteService(Service $service)
    {
        $service->delete();

        return back()->with('success', 'Paket jasa berhasil dihapus.');
    }

    private function validatedService(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'starting_price' => 'required|numeric|min:0|max:9999999999',
            'icon' => 'nullable|string|max:100',
        ]);
    }

    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:3000',
            'link' => 'nullable|url|max:2048',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('projects', 'public');
        }

        Project::create($data);

        return back()->with('success', 'Proyek berhasil ditambahkan ke portofolio.');
    }

    public function deleteProject(Project $project)
    {
        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }
        $project->delete();

        return back()->with('success', 'Proyek berhasil dihapus.');
    }

    public function uploadCv(Request $request)
    {
        $request->validate(['cv' => 'required|file|mimes:pdf|max:10240']);
        $previous = PortfolioSetting::where('key', 'cv_path')->value('value');
        $path = $request->file('cv')->store('cv', 'public');
        PortfolioSetting::updateOrCreate(['key' => 'cv_path'], ['value' => $path]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return back()->with('success', 'File CV berhasil diperbarui.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portfolio_admin');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
