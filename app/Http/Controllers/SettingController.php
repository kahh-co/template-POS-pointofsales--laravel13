<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $setting = Setting::current();

        return view('settings.index', [
            'setting' => $setting,
            'settings' => $setting,
        ]);
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'qris_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $setting = Setting::current();

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $validated['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('qris_image')) {
            if ($setting->qris_image) {
                Storage::disk('public')->delete($setting->qris_image);
            }
            $validated['qris_image'] = $request->file('qris_image')->store('settings', 'public');
        }

        $setting->update($validated);

        return back()->with('success', 'Informasi toko berhasil diperbarui.')->with('settings_tab', 'store');
    }

    public function updateReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'paper_size' => ['required', 'string', 'max:20'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::current()->update($validated);

        return back()->with('success', 'Pengaturan struk berhasil diperbarui.')->with('settings_tab', 'receipt');
    }

    public function updateTax(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_enabled' => ['nullable', 'boolean'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::current()->update([
            'tax_enabled' => $request->boolean('tax_enabled'),
            'tax_percent' => $validated['tax_percent'] ?? 0,
        ]);

        return back()->with('success', 'Pengaturan pajak berhasil diperbarui.')->with('settings_tab', 'tax');
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user = $request->user();
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Akun berhasil diperbarui.')->with('settings_tab', 'account');
    }
}
