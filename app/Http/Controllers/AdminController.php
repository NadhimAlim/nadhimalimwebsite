<?php

namespace App\Http\Controllers;

use App\Models\PortfolioSetting;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\News;
use App\Models\WorkTask;
use App\Models\ProjectPayment;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        if (!$this->adminPasswordMatches($request->password)) {
            return back()->withErrors(['password' => 'Kata sandi tidak cocok.'])->onlyInput();
        }

        $request->session()->regenerate();
        $request->session()->put('portfolio_admin', true);

        return redirect()->route('admin.dashboard');
    }

    public function dashboard(string $section = 'overview')
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

        $profileContent = PortfolioSetting::profileContent();
        if ($profileDraft = session('profile_preview')) {
            $profileContent = array_replace($profileContent, $profileDraft);
        }

        return view('admin.dashboard', [
            'section' => $section,
            'projects' => Project::orderBy('sort_order')->orderByDesc('created_at')->get(),
            'services' => Service::orderBy('id')->get(),
            'skills' => Skill::orderBy('type')->orderBy('sort_order')->orderBy('name')->get(),
            'contactMessages' => ContactMessage::latest()->get(),
            'unreadMessageCount' => ContactMessage::where('is_read', false)->count(),
            'profileContent' => $profileContent,
            'cvPath' => PortfolioSetting::where('key', 'cv_path')->value('value'),
            'profilePhoto' => PortfolioSetting::where('key', 'profile_photo')->value('value'),
            'educations' => Education::orderBy('sort_order')->get(),
            'newsArticles' => News::orderByDesc('created_at')->get(),
            'adminProfile' => [
                'name' => PortfolioSetting::where('key', 'admin_name')->value('value') ?: 'Nadhim Alim',
                'photo' => PortfolioSetting::where('key', 'admin_photo')->value('value'),
                'whatsapp' => PortfolioSetting::where('key', 'admin_whatsapp')->value('value') ?: config('portfolio.whatsapp'),
            ],
            'workTasks' => WorkTask::with('payments')->orderBy('scheduled_at')->get(),
            'midtransConfigured' => filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key')),
            'marketplaceProducts' => MarketplaceProduct::orderBy('sort_order')->orderByDesc('created_at')->get(),
            'marketplaceOrders' => MarketplaceOrder::latest()->get(),
            'upcomingTaskCount' => WorkTask::where('status', '!=', 'done')->where('scheduled_at', '>=', now())->count(),
            'overdueDeadlineCount' => WorkTask::where('status', '!=', 'done')->whereNotNull('deadline_at')->where('deadline_at', '<', now())->count(),
            'nearDeadlineCount' => WorkTask::where('status', '!=', 'done')->whereBetween('deadline_at', [now(), now()->copy()->addDays(3)])->count(),
        ]);
    }

    private function adminPasswordMatches(string $provided): bool
    {
        $storedHash = PortfolioSetting::where('key', 'admin_password_hash')->value('value');
        if ($storedHash) return Hash::check($provided, $storedHash);
        $configured = config('portfolio.admin_password');
        return (bool) $configured && hash_equals($configured, $provided);
    }

    public function updateAdminProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]*$/'],
        ]);
        PortfolioSetting::updateOrCreate(['key' => 'admin_name'], ['value' => $data['name']]);
        $whatsapp = preg_replace('/\D+/', '', $data['whatsapp'] ?? '');
        PortfolioSetting::updateOrCreate(['key' => 'admin_whatsapp'], ['value' => $whatsapp]);
        if ($request->hasFile('photo')) {
            $previous = PortfolioSetting::where('key', 'admin_photo')->value('value');
            $path = $request->file('photo')->store('admin', 'public');
            PortfolioSetting::updateOrCreate(['key' => 'admin_photo'], ['value' => $path]);
            if ($previous) Storage::disk('public')->delete($previous);
        }
        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function changeAdminPassword(Request $request)
    {
        $data = $request->validate(['current_password' => 'required|string', 'new_password' => 'required|string|min:10|confirmed']);
        if (!$this->adminPasswordMatches($data['current_password'])) return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        PortfolioSetting::updateOrCreate(['key' => 'admin_password_hash'], ['value' => Hash::make($data['new_password'])]);
        return back()->with('success', 'Kata sandi admin berhasil diubah.');
    }

    private function validatedWorkTask(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'client' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:3000', 'scheduled_at' => 'required|date',
            'deadline_at' => 'nullable|date|after_or_equal:scheduled_at',
            'status' => 'required|in:planned,in_progress,done', 'priority' => 'required|in:low,normal,high',
            'progress_notes' => 'nullable|string|max:5000',
            'project_value' => 'nullable|numeric|min:0|max:999999999999.99',
            'amount_paid' => 'nullable|numeric|min:0|max:999999999999.99',
            'payment_status' => 'required|in:unpaid,partial,paid',
        ]);
        if (($data['amount_paid'] ?? 0) > ($data['project_value'] ?? 0)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount_paid' => 'Jumlah yang sudah dibayar tidak boleh melebihi nilai proyek.']);
        }
        if ($data['payment_status'] === 'paid' && ($data['amount_paid'] ?? 0) < ($data['project_value'] ?? 0)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['payment_status' => 'Status lunas memerlukan jumlah dibayar sebesar nilai proyek.']);
        }
        return $data;
    }

    public function storeWorkTask(Request $request)
    {
        WorkTask::create($this->validatedWorkTask($request));
        return back()->with('success', 'Jadwal proyek berhasil ditambahkan.');
    }

    public function updateWorkTask(Request $request, WorkTask $workTask)
    {
        $workTask->update($this->validatedWorkTask($request));
        return back()->with('success', 'Jadwal proyek berhasil diperbarui.');
    }

    public function deleteWorkTask(WorkTask $workTask)
    {
        if ($workTask->payments()->exists()) {
            return back()->withErrors(['payment' => 'Jadwal ini memiliki riwayat tagihan online. Riwayat pembayaran harus tetap tersimpan.']);
        }
        $workTask->delete();
        return back()->with('success', 'Jadwal proyek berhasil dihapus.');
    }

    public function exportWorkTasks()
    {
        $tasks = WorkTask::orderBy('scheduled_at')->get();
        return response()->streamDownload(function () use ($tasks) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Pekerjaan', 'Klien', 'Jadwal', 'Deadline', 'Status pekerjaan', 'Prioritas', 'Catatan progres', 'Nilai proyek (Rp)', 'Dibayar (Rp)', 'Sisa (Rp)', 'Status pembayaran']);
            foreach ($tasks as $task) {
                fputcsv($output, [
                    $this->csvSafe($task->title), $this->csvSafe($task->client), $task->scheduled_at?->format('Y-m-d H:i'),
                    $task->deadline_at?->format('Y-m-d H:i'), ['planned' => 'Terencana', 'in_progress' => 'Dikerjakan', 'done' => 'Selesai'][$task->status] ?? $task->status,
                    ['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi'][$task->priority] ?? $task->priority,
                    $this->csvSafe($task->progress_notes), $task->project_value, $task->amount_paid,
                    max(0, (float) $task->project_value - (float) $task->amount_paid),
                    ['unpaid' => 'Belum dibayar', 'partial' => 'Sebagian', 'paid' => 'Lunas'][$task->payment_status] ?? $task->payment_status,
                ]);
            }
            fclose($output);
        }, 'jadwal-dan-pembayaran.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvSafe(?string $value): string
    {
        $value = $value ?? '';
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    public function downloadBackup()
    {
        $zipPath = tempnam(storage_path('app'), 'portfolio-backup-');
        $zip = new \ZipArchive();
        if ($zipPath === false || $zip->open($zipPath, \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'File backup tidak dapat dibuat.');
        }

        $backup = ['format' => 'nadhim-portfolio-backup', 'version' => 1, 'created_at' => now()->toIso8601String(), 'projects' => [], 'news' => [], 'work_tasks' => [], 'project_payments' => [], 'marketplace_products' => [], 'marketplace_orders' => []];
        foreach (Project::orderBy('id')->get() as $project) {
            $backup['projects'][] = $this->backupRow($project->getAttributes(), $project->image, 'projects', $zip);
        }
        foreach (News::orderBy('id')->get() as $news) {
            $backup['news'][] = $this->backupRow($news->getAttributes(), $news->image, 'news', $zip);
        }
        $backup['work_tasks'] = WorkTask::orderBy('id')->get()->map(fn ($task) => $task->getAttributes())->all();
        $backup['project_payments'] = ProjectPayment::orderBy('id')->get()->map(fn ($payment) => $payment->getAttributes())->all();
        foreach (MarketplaceProduct::orderBy('id')->get() as $product) {
            $backup['marketplace_products'][] = $this->backupRow($product->getAttributes(), $product->image, 'marketplace', $zip);
        }
        $backup['marketplace_orders'] = MarketplaceOrder::orderBy('id')->get()->map(fn ($order) => $order->getAttributes())->all();
        $zip->addFromString('backup.json', json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $zip->close();

        return response()->download($zipPath, 'nadhim-portfolio-backup-' . now()->format('Y-m-d') . '.zip')->deleteFileAfterSend(true);
    }

    public function restoreBackup(Request $request)
    {
        $request->validate(['backup' => 'required|file|mimes:zip|max:102400']);
        $zip = new \ZipArchive();
        if ($zip->open($request->file('backup')->getRealPath()) !== true) {
            return back()->withErrors(['backup' => 'Arsip ZIP tidak dapat dibuka.']);
        }

        try {
            $uncompressedSize = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $uncompressedSize += $stat['size'] ?? 0;
                if ($uncompressedSize > 536870912 || $zip->numFiles > 2500) {
                    return back()->withErrors(['backup' => 'Ukuran isi backup terlalu besar. Maksimal 512 MB setelah diekstrak.']);
                }
            }

            $json = $zip->getFromName('backup.json');
            try {
                $backup = $json ? json_decode($json, true, 512, JSON_THROW_ON_ERROR) : null;
            } catch (\JsonException) {
                $backup = null;
            }
            if (!is_array($backup) || ($backup['format'] ?? null) !== 'nadhim-portfolio-backup' || ($backup['version'] ?? null) !== 1) {
                return back()->withErrors(['backup' => 'Format backup tidak dikenali atau versinya tidak didukung.']);
            }
            foreach (['projects', 'news', 'work_tasks'] as $key) {
                if (!isset($backup[$key]) || !is_array($backup[$key]) || count($backup[$key]) > 10000) {
                    return back()->withErrors(['backup' => 'Struktur data backup tidak valid.']);
                }
            }
            $backup['project_payments'] ??= [];
            if (!is_array($backup['project_payments']) || count($backup['project_payments']) > 10000) {
                return back()->withErrors(['backup' => 'Struktur data pembayaran dalam backup tidak valid.']);
            }
            $backup['marketplace_products'] ??= [];
            $backup['marketplace_orders'] ??= [];
            if (!is_array($backup['marketplace_products']) || !is_array($backup['marketplace_orders']) || count($backup['marketplace_products']) > 10000 || count($backup['marketplace_orders']) > 10000) {
                return back()->withErrors(['backup' => 'Struktur data marketplace dalam backup tidak valid.']);
            }

            $counts = DB::transaction(function () use ($backup, $zip) {
                $counts = ['projects' => 0, 'news' => 0, 'work_tasks' => 0, 'project_payments' => 0, 'marketplace_products' => 0, 'marketplace_orders' => 0];
                $taskMap = [];
                $productMap = [];
                foreach ($backup['projects'] as $row) {
                    $this->validateBackupRow($row, ['title' => 'required|string|max:255', 'category' => 'required|string|max:100', 'description' => 'required|string|max:3000', 'link' => 'nullable|url|max:2048', 'sort_order' => 'nullable|integer|min:0']);
                    $model = Project::firstOrCreate(
                        ['title' => $row['title'], 'category' => $row['category']],
                        ['description' => $row['description'], 'link' => $row['link'] ?? null, 'sort_order' => $row['sort_order'] ?? 0]
                    );
                    $counts['projects'] += (int) $model->wasRecentlyCreated;
                    $this->restoreBackupImage($zip, $row, $model, 'projects');
                }
                foreach ($backup['news'] as $row) {
                    $this->validateBackupRow($row, ['title' => 'required|string|max:255', 'slug' => 'required|string|max:255', 'excerpt' => 'required|string|max:500', 'content' => 'required|string|max:30000', 'published_at' => 'nullable|date']);
                    $model = News::firstOrCreate(['slug' => $row['slug']], ['title' => $row['title'], 'excerpt' => $row['excerpt'], 'content' => $row['content'], 'published_at' => $row['published_at'] ?? null]);
                    $counts['news'] += (int) $model->wasRecentlyCreated;
                    $this->restoreBackupImage($zip, $row, $model, 'news');
                }
                foreach ($backup['work_tasks'] as $row) {
                    $this->validateBackupRow($row, [
                        'title' => 'required|string|max:255', 'client' => 'nullable|string|max:255', 'description' => 'nullable|string|max:3000',
                        'scheduled_at' => 'required|date', 'deadline_at' => 'nullable|date', 'status' => 'required|in:planned,in_progress,done',
                        'priority' => 'required|in:low,normal,high', 'progress_notes' => 'nullable|string|max:5000',
                        'project_value' => 'nullable|numeric|min:0', 'amount_paid' => 'nullable|numeric|min:0', 'payment_status' => 'required|in:unpaid,partial,paid',
                    ]);
                    $model = WorkTask::firstOrCreate(
                        ['title' => $row['title'], 'client' => $row['client'] ?? null, 'scheduled_at' => $row['scheduled_at']],
                        collect($row)->only(['description', 'deadline_at', 'status', 'priority', 'progress_notes', 'project_value', 'amount_paid', 'payment_status'])->all()
                    );
                    $counts['work_tasks'] += (int) $model->wasRecentlyCreated;
                    $taskMap[(string) ($row['id'] ?? '')] = $model->id;
                }
                foreach ($backup['project_payments'] as $row) {
                    $this->validateBackupRow($row, [
                        'work_task_id' => 'required|integer|min:1', 'public_token' => 'required|string|max:64', 'order_id' => 'required|string|max:64',
                        'customer_name' => 'required|string|max:120', 'customer_email' => 'nullable|email|max:190', 'gross_amount' => 'required|integer|min:1',
                        'snap_token' => 'nullable|string|max:255', 'redirect_url' => 'nullable|url|max:2048', 'transaction_id' => 'nullable|string|max:255',
                        'payment_type' => 'nullable|string|max:80', 'status' => 'required|in:pending,challenge,paid,denied,cancelled,expired',
                        'notification_payload' => 'nullable|array', 'paid_at' => 'nullable|date', 'expires_at' => 'nullable|date',
                    ]);
                    $mappedTaskId = $taskMap[(string) $row['work_task_id']] ?? null;
                    if (!$mappedTaskId) continue;
                    $model = ProjectPayment::firstOrCreate(['order_id' => $row['order_id']], [
                        'work_task_id' => $mappedTaskId, 'public_token' => $row['public_token'], 'customer_name' => $row['customer_name'],
                        'customer_email' => $row['customer_email'] ?? null, 'gross_amount' => $row['gross_amount'], 'snap_token' => $row['snap_token'] ?? null,
                        'redirect_url' => $row['redirect_url'] ?? null, 'transaction_id' => $row['transaction_id'] ?? null, 'payment_type' => $row['payment_type'] ?? null,
                        'status' => $row['status'], 'notification_payload' => $row['notification_payload'] ?? null, 'paid_at' => $row['paid_at'] ?? null,
                        'expires_at' => $row['expires_at'] ?? null, 'created_at' => $row['created_at'] ?? now(), 'updated_at' => $row['updated_at'] ?? now(),
                    ]);
                    $counts['project_payments'] += (int) $model->wasRecentlyCreated;
                }
                foreach ($backup['marketplace_products'] as $row) {
                    $this->validateBackupRow($row, [
                        'name' => 'required|string|max:180', 'slug' => 'required|string|max:255', 'category' => 'nullable|string|max:100',
                        'description' => 'required|string|max:5000', 'price' => 'required|integer|min:1000', 'stock' => 'required|integer|min:0',
                        'is_active' => 'required|boolean', 'sort_order' => 'nullable|integer|min:0',
                    ]);
                    $model = MarketplaceProduct::firstOrCreate(['slug' => $row['slug']], collect($row)->only(['name', 'category', 'description', 'price', 'stock', 'is_active', 'sort_order'])->all());
                    $productMap[(string) ($row['id'] ?? '')] = $model->id;
                    $counts['marketplace_products'] += (int) $model->wasRecentlyCreated;
                    $this->restoreBackupImage($zip, $row, $model, 'marketplace');
                }
                foreach ($backup['marketplace_orders'] as $row) {
                    $this->validateBackupRow($row, [
                        'marketplace_product_id' => 'nullable|integer|min:1', 'public_token' => 'required|string|max:64', 'order_id' => 'required|string|max:64',
                        'product_name' => 'required|string|max:255', 'unit_price' => 'required|integer|min:1', 'quantity' => 'required|integer|min:1',
                        'total_amount' => 'required|integer|min:1', 'customer_name' => 'required|string|max:120', 'customer_email' => 'nullable|email|max:190',
                        'customer_phone' => 'required|string|max:30', 'shipping_address' => 'required|string|max:1500',
                        'status' => 'required|string|max:30', 'payment_status' => 'required|string|max:30', 'snap_token' => 'nullable|string|max:255',
                        'redirect_url' => 'nullable|url|max:2048', 'transaction_id' => 'nullable|string|max:255', 'payment_type' => 'nullable|string|max:80',
                        'notification_payload' => 'nullable|array', 'paid_at' => 'nullable|date',
                    ]);
                    $productId = isset($row['marketplace_product_id']) ? ($productMap[(string) $row['marketplace_product_id']] ?? null) : null;
                    $model = MarketplaceOrder::firstOrCreate(['order_id' => $row['order_id']], [
                        'marketplace_product_id' => $productId, 'public_token' => $row['public_token'], 'product_name' => $row['product_name'],
                        'unit_price' => $row['unit_price'], 'quantity' => $row['quantity'], 'total_amount' => $row['total_amount'],
                        'customer_name' => $row['customer_name'], 'customer_email' => $row['customer_email'] ?? null, 'customer_phone' => $row['customer_phone'],
                        'shipping_address' => $row['shipping_address'], 'status' => $row['status'], 'payment_status' => $row['payment_status'],
                        'snap_token' => $row['snap_token'] ?? null, 'redirect_url' => $row['redirect_url'] ?? null,
                        'transaction_id' => $row['transaction_id'] ?? null, 'payment_type' => $row['payment_type'] ?? null,
                        'notification_payload' => $row['notification_payload'] ?? null, 'paid_at' => $row['paid_at'] ?? null,
                        'created_at' => $row['created_at'] ?? now(), 'updated_at' => $row['updated_at'] ?? now(),
                    ]);
                    $counts['marketplace_orders'] += (int) $model->wasRecentlyCreated;
                }
                return $counts;
            });

            return back()->with('success', 'Pemulihan selesai. Ditambahkan ' . $counts['projects'] . ' proyek, ' . $counts['news'] . ' berita, ' . $counts['work_tasks'] . ' agenda, ' . $counts['project_payments'] . ' riwayat pembayaran proyek, ' . $counts['marketplace_products'] . ' barang, dan ' . $counts['marketplace_orders'] . ' pesanan marketplace. Data yang sudah ada tidak ditimpa.');
        } finally {
            $zip->close();
        }
    }

    private function validateBackupRow(mixed $row, array $rules): void
    {
        if (!is_array($row) || Validator::make($row, $rules)->fails()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['backup' => 'Backup berisi data yang tidak valid; tidak ada data yang dipulihkan.']);
        }
    }

    private function restoreBackupImage(\ZipArchive $zip, array $row, Project|News|MarketplaceProduct $model, string $directory): void
    {
        $originalPath = $row['_image_path'] ?? null;
        $entry = $row['_image_entry'] ?? null;
        if (!$originalPath || !$entry || $model->image || !is_string($originalPath) || !is_string($entry)) return;
        if (!preg_match('#^' . preg_quote($directory, '#') . '/[A-Za-z0-9._/-]+$#D', $originalPath) || preg_match('#(^|/)\.\.(/|$)#', $originalPath)) return;
        if (!preg_match('#^media/' . preg_quote($directory, '#') . '/[a-f0-9]{64}\.(jpg|jpeg|png|webp)$#D', $entry)) return;

        $contents = $zip->getFromName($entry);
        $image = $contents !== false ? @getimagesizefromstring($contents) : false;
        if (!$image || !in_array($image['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)) return;
        $extension = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));
        $expectedMime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$extension] ?? null;
        if ($expectedMime !== ($image['mime'] ?? null)) return;
        $target = $directory . '/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($target, $contents);
        $model->update(['image' => $target]);
    }

    private function backupRow(array $attributes, ?string $imagePath, string $directory, \ZipArchive $zip): array
    {
        $attributes['_image_path'] = $imagePath;
        $attributes['_image_entry'] = null;
        if ($imagePath && str_starts_with($imagePath, $directory . '/') && !str_contains($imagePath, '..') && Storage::disk('public')->exists($imagePath)) {
            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $entry = 'media/' . $directory . '/' . hash('sha256', $imagePath) . '.' . $extension;
                $absolutePath = Storage::disk('public')->path($imagePath);
                if (is_file($absolutePath)) {
                    $zip->addFile($absolutePath, $entry);
                    $attributes['_image_entry'] = $entry;
                }
            }
        }
        return $attributes;
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

    public function storeSkill(Request $request)
    {
        $data = $this->validatedSkill($request);
        $data['sort_order'] = (Skill::where('type', $data['type'])->max('sort_order') ?? -1) + 1;
        Skill::create($data);

        return back()->with('success', 'Keahlian berhasil ditambahkan.');
    }

    public function updateSkill(Request $request, Skill $skill)
    {
        $data = $this->validatedSkill($request);
        if ($data['type'] !== $skill->type) {
            $data['sort_order'] = (Skill::where('type', $data['type'])->max('sort_order') ?? -1) + 1;
        }
        $skill->update($data);

        return back()->with('success', 'Keahlian berhasil diperbarui.');
    }

    public function deleteSkill(Skill $skill)
    {
        $skill->delete();

        return back()->with('success', 'Keahlian berhasil dihapus.');
    }

    public function deleteContactMessage(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }

    public function updateContactMessageStatus(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate(['is_read' => 'required|boolean']);
        $data['is_read'] = filter_var($data['is_read'], FILTER_VALIDATE_BOOLEAN);
        $contactMessage->update($data);

        return back()->with('success', $data['is_read'] ? 'Pesan ditandai sudah dibaca.' : 'Pesan ditandai belum dibaca.');
    }

    public function previewProfile(Request $request)
    {
        $profile = $request->validate([
            'hero_intro' => 'required|string|max:120',
            'hero_title' => 'required|string|max:180',
            'hero_description' => 'required|string|max:500',
            'about_title' => 'required|string|max:180',
            'about_paragraph_1' => 'required|string|max:1000',
            'about_paragraph_2' => 'required|string|max:1000',
            'youtube_url' => 'nullable|url|max:2048',
            'instagram_url' => 'nullable|url|max:2048',
            'tiktok_url' => 'nullable|url|max:2048',
        ]);

        $request->session()->put('profile_preview', $profile);

        return redirect()->route('home', ['preview' => 1]);
    }

    public function publishProfilePreview(Request $request)
    {
        $profile = $request->session()->get('profile_preview');
        if (!$profile) {
            return redirect()->route('admin.dashboard.section', 'profile')->withErrors(['profile' => 'Pratinjau sudah kedaluwarsa. Silakan coba lagi.']);
        }

        foreach ($profile as $key => $value) {
            PortfolioSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $request->session()->forget('profile_preview');

        return redirect()->route('admin.dashboard.section', 'profile')->with('success', 'Konten profil berhasil dipublikasikan.');
    }

    public function discardProfilePreview(Request $request)
    {
        $request->session()->forget('profile_preview');

        return redirect()->route('admin.dashboard.section', 'profile')->with('success', 'Pratinjau dibuang. Konten yang dipublikasikan tidak berubah.');
    }

    public function moveProject(Request $request, Project $project)
    {
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $this->swapOrder(Project::orderBy('sort_order')->orderBy('id')->get(), $project, $data['direction']);

        return back()->with('success', 'Urutan proyek berhasil diperbarui.');
    }

    public function moveSkill(Request $request, Skill $skill)
    {
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $siblings = Skill::where('type', $skill->type)->orderBy('sort_order')->orderBy('name')->get();
        $this->swapOrder($siblings, $skill, $data['direction']);

        return back()->with('success', 'Urutan keahlian berhasil diperbarui.');
    }

    public function updateEducation(Request $request, Education $education)
    {
        $data = $request->validate([
            'institution' => 'nullable|string|max:255',
            'start_year' => 'nullable|required_with:end_year|integer|min:1900|max:2100',
            'end_year' => 'nullable|integer|gte:start_year|min:1900|max:2100',
            'description' => 'nullable|string|max:500',
        ]);

        if (!$data['institution']) {
            $data = ['institution' => null, 'start_year' => null, 'end_year' => null, 'description' => null];
        }

        $education->update($data);

        return back()->with('success', 'Riwayat pendidikan berhasil diperbarui.');
    }

    public function storeNews(Request $request)
    {
        $data = $this->validatedNews($request);
        $data['slug'] = $this->uniqueNewsSlug($data['title']);
        $data['published_at'] = $data['is_published'] ? now() : null;
        unset($data['is_published']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        News::create($data);

        return back()->with('success', 'Berita berhasil disimpan.');
    }

    public function updateNews(Request $request, News $news)
    {
        $data = $this->validatedNews($request);
        $data['slug'] = $this->uniqueNewsSlug($data['title'], $news->id);
        $data['published_at'] = $data['is_published'] ? ($news->published_at ?: now()) : null;
        unset($data['is_published']);

        if ($request->hasFile('image')) {
            $previous = $news->image;
            $data['image'] = $request->file('image')->store('news', 'public');
            if ($previous) {
                Storage::disk('public')->delete($previous);
            }
        }

        $news->update($data);

        return back()->with('success', 'Berita berhasil diperbarui.');
    }

    public function deleteNews(News $news)
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }
        $news->delete();

        return back()->with('success', 'Berita berhasil dihapus.');
    }

    private function validatedNews(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string|max:30000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'required|boolean',
        ]);
    }

    private function uniqueNewsSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'berita';
        $slug = $base;
        $suffix = 2;

        while (News::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function swapOrder($items, $item, string $direction): void
    {
        $index = $items->search(fn ($candidate) => $candidate->getKey() == $item->getKey());
        if ($index === false) {
            return;
        }

        $neighborIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if (!$items->has($neighborIndex)) {
            return;
        }

        $neighbor = $items->get($neighborIndex);
        DB::transaction(function () use ($item, $neighbor) {
            $order = $item->sort_order;
            $item->update(['sort_order' => $neighbor->sort_order]);
            $neighbor->update(['sort_order' => $order]);
        });
    }

    private function validatedSkill(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:hard,soft',
            'level' => 'required|integer|min:1|max:100',
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
        $data['sort_order'] = (Project::max('sort_order') ?? -1) + 1;

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

    public function updateProject(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:3000',
            'link' => 'nullable|url|max:2048',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $previous = $project->image;
            $data['image'] = $request->file('image')->store('projects', 'public');
            if ($previous) {
                Storage::disk('public')->delete($previous);
            }
        }

        $project->update($data);

        return back()->with('success', 'Proyek dan foto sampul berhasil diperbarui.');
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

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        $previous = PortfolioSetting::where('key', 'profile_photo')->value('value');
        $path = $request->file('photo')->store('profile', 'public');
        PortfolioSetting::updateOrCreate(['key' => 'profile_photo'], ['value' => $path]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portfolio_admin');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
