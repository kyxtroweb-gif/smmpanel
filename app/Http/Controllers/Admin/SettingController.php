<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function getIndex(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function postSave(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'timezone' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
            'min_deposit' => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'refill_days' => 'nullable|integer|min:0',
            'maintenance_mode' => 'nullable|boolean',
            'registration_enabled' => 'nullable|boolean',
            'default_user_balance' => 'nullable|numeric|min:0',
        ]);
        $v->validate();

        $fields = [
            'site_name', 'site_tagline', 'contact_email', 'support_email',
            'timezone', 'currency', 'currency_symbol', 'min_deposit',
            'min_order_value', 'refill_days', 'maintenance_mode',
            'registration_enabled', 'default_user_balance',
        ];

        DB::beginTransaction();
        try {
            foreach ($fields as $key) {
                $value = $request->input($key);
                if ($key === 'maintenance_mode' || $key === 'registration_enabled') {
                    $value = $value ? '1' : '0';
                }
                Setting::set($key, $value ?? '', is_numeric($value) ? 'int' : 'string');
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'settings_updated',
                'description' => 'Updated site settings',
            ]);

            DB::commit();
            return back()->with('success', 'Settings saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getSeo(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.seo', compact('settings'));
    }

    public function postSeo(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_og_image' => 'nullable|url|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
        ]);
        $v->validate();

        $fields = ['seo_title', 'seo_description', 'seo_keywords', 'seo_og_image',
                   'google_analytics_id', 'google_tag_manager_id'];

        DB::beginTransaction();
        try {
            foreach ($fields as $key) {
                Setting::set($key, $request->input($key) ?? '');
            }
            DB::commit();
            return back()->with('success', 'SEO settings saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getTheme(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.theme', compact('settings'));
    }

    public function postTheme(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'primary_color' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo_text' => 'nullable|string|max:100',
            'footer_text' => 'nullable|string|max:500',
            'custom_css' => 'nullable|string',
        ]);
        $v->validate();

        $fields = ['primary_color', 'secondary_color', 'logo_text', 'footer_text', 'custom_css'];

        DB::beginTransaction();
        try {
            foreach ($fields as $key) {
                Setting::set($key, $request->input($key) ?? '');
            }

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('settings', 'public');
                Setting::set('logo_path', $path);
            }

            DB::commit();
            return back()->with('success', 'Theme settings saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
