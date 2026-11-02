<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Sale;
use App\Models\User;
use App\Services\SystemTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branches = Branch::withCount('sales')->get();
        $allUsers = [];

        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if ($isOwnerOrAdmin) {
            $allUsers = User::with(['roles', 'branch'])->where('id', '!=', $user->id)->get();
        }

        $businessName = BusinessSetting::get('business_name', 'Tshaprint Solutions');
        $businessTagline = BusinessSetting::get('business_tagline', 'Commercial Retail & Multi-Branch POS');
        $businessEmail = BusinessSetting::get('business_email', 'sales@tshaprint.co.ke');
        $businessPhone = BusinessSetting::get('business_phone', '+254 700 000 000');
        $businessAddress = BusinessSetting::get('business_address', 'Nairobi, Kenya');
        $businessCurrency = BusinessSetting::get('business_currency', 'KES');

        // Dynamic Commercial & Tier Settings
        $bulkDiscount = (float) BusinessSetting::get('pricing_tier_bulk_discount', 10.0);
        $wholesaleDiscount = (float) BusinessSetting::get('pricing_tier_wholesale_discount', 15.0);
        $bulkLabel = BusinessSetting::get('pricing_tier_bulk_label', 'School Bulk Tier');
        $wholesaleLabel = BusinessSetting::get('pricing_tier_wholesale_label', 'Wholesale Contract Tier');
        $kraPin = BusinessSetting::get('kra_pin', '');
        $vatNumber = BusinessSetting::get('vat_number', '');
        $mpesaTill = BusinessSetting::get('mpesa_till_number', '');
        $mpesaPaybill = BusinessSetting::get('mpesa_paybill_number', '');
        $receiptHeader = BusinessSetting::get('receipt_header_note', '');
        $receiptFooter = BusinessSetting::get('receipt_footer_note', 'Goods once sold are returnable within 7 days with valid receipt.');

        $currentSystemTime = SystemTime::formatted('d M Y, h:i A');
        $currentSystemTimeIso = SystemTime::now()->format('Y-m-d\TH:i');
        $hasTimeOffset = SystemTime::hasOffset();
        $offsetSeconds = SystemTime::getOffsetSeconds();

        return view('settings.index', compact(
            'user',
            'branches',
            'allUsers',
            'isOwnerOrAdmin',
            'businessName',
            'businessTagline',
            'businessEmail',
            'businessPhone',
            'businessAddress',
            'businessCurrency',
            'bulkDiscount',
            'wholesaleDiscount',
            'bulkLabel',
            'wholesaleLabel',
            'kraPin',
            'vatNumber',
            'mpesaTill',
            'mpesaPaybill',
            'receiptHeader',
            'receiptFooter',
            'currentSystemTime',
            'currentSystemTimeIso',
            'hasTimeOffset',
            'offsetSeconds'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'username'         => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $user->id],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password'     => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name     = $validated['name'];
        $user->username = strtolower($validated['username']);
        $user->email    = $validated['email'];

        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return back()->with('success', 'Profile and security credentials updated successfully.');
    }

    public function updateBusinessInfo(Request $request)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Only business owners and system administrators can update store information.');
        }

        $validated = $request->validate([
            'business_name'                  => ['required', 'string', 'max:255'],
            'business_tagline'               => ['nullable', 'string', 'max:255'],
            'business_email'                 => ['nullable', 'email', 'max:255'],
            'business_phone'                 => ['nullable', 'string', 'max:50'],
            'business_address'               => ['nullable', 'string', 'max:255'],
            'business_currency'              => ['required', 'string', 'max:10'],
            'kra_pin'                        => ['nullable', 'string', 'max:50'],
            'vat_number'                     => ['nullable', 'string', 'max:50'],
            'mpesa_till_number'              => ['nullable', 'string', 'max:50'],
            'mpesa_paybill_number'           => ['nullable', 'string', 'max:50'],
            'receipt_header_note'            => ['nullable', 'string', 'max:255'],
            'receipt_footer_note'            => ['nullable', 'string', 'max:255'],
            'pricing_tier_bulk_discount'     => ['required', 'numeric', 'min:0', 'max:99'],
            'pricing_tier_wholesale_discount'=> ['required', 'numeric', 'min:0', 'max:99'],
            'pricing_tier_bulk_label'        => ['required', 'string', 'max:50'],
            'pricing_tier_wholesale_label'   => ['required', 'string', 'max:50'],
        ]);

        foreach ($validated as $key => $value) {
            BusinessSetting::set($key, $value);
        }

        return back()->with('success', 'Store profile and commercial details updated successfully.');
    }

    public function storeBranch(Request $request)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Only business owners and system administrators can add new branches.');
        }

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100', 'unique:branches,name'],
            'code'    => ['nullable', 'string', 'max:30', 'unique:branches,code'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
        ]);

        Branch::create([
            'name'      => $validated['name'],
            'code'      => $validated['code'] ?? null,
            'address'   => $validated['address'] ?? null,
            'phone'     => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Branch '{$validated['name']}' created and activated for operations.");
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100', 'unique:branches,name,' . $branch->id],
            'code'    => ['nullable', 'string', 'max:30', 'unique:branches,code,' . $branch->id],
            'address' => ['nullable', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
        ]);

        $oldName = $branch->name;
        $branch->update($validated);

        if ($oldName !== $validated['name']) {
            User::where('branch_id', $branch->id)->update(['branch_name' => $validated['name']]);
            Sale::where('branch_name', $oldName)->update(['branch_name' => $validated['name']]);
        }

        return back()->with('success', "Branch '{$branch->name}' details updated successfully.");
    }

    public function toggleBranch(Branch $branch)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Unauthorized.');
        }

        $branch->update(['is_active' => !$branch->is_active]);
        $status = $branch->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Branch '{$branch->name}' {$status} successfully.");
    }

    public function destroyBranch(Branch $branch)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Unauthorized.');
        }

        $hasSales = Sale::where('branch_name', $branch->name)->exists();
        $hasUsers = User::where('branch_id', $branch->id)->exists();

        if ($hasSales || $hasUsers) {
            return back()->with('error', "Cannot delete '{$branch->name}' because it contains active historical transactions or assigned staff. Deactivate the branch instead to preserve financial records.");
        }

        $branchName = $branch->name;
        $branch->delete();

        return back()->with('success', "Branch '{$branchName}' deleted successfully.");
    }

    public function updateSystemTime(Request $request)
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Only business owners and system administrators can calibrate system date and time.');
        }

        $request->validate([
            'target_datetime' => ['required', 'string'],
        ]);

        SystemTime::setTargetDateTime($request->target_datetime);

        return back()->with('success', 'Authoritative operational system time updated: ' . SystemTime::formatted());
    }

    public function resetSystemTime()
    {
        $user = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($user->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'Unauthorized.');
        }

        SystemTime::resetToNetworkTime();

        return back()->with('success', 'System time reset to official network time (Africa/Nairobi): ' . SystemTime::formatted());
    }

    public function deleteUser(User $user)
    {
        $currentUser = auth()->user();
        $isOwnerOrAdmin = in_array(strtolower($currentUser->roles->first()?->name ?? ''), ['owner', 'system_administrator', 'admin']);
        if (!$isOwnerOrAdmin) {
            abort(403, 'You do not have permission to delete user accounts.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own active account.');
        }

        if ($user->hasRole('system_administrator') || $user->hasRole('owner')) {
            return back()->with('error', 'System Administrator and Owner accounts cannot be deleted for system safety.');
        }

        $userName = $user->name;
        $user->delete();

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "Staff account '{$userName}' has been deleted successfully.");
    }

    public function switchAccount(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Account session closed. Enter username and password to log in.');
    }
}
