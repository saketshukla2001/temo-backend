<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\BaseController;
use App\Models\Admin\PlatformFee;
use Illuminate\Http\Request;

class PlatformFeeController extends BaseController
{
    public function index()
    {
        $page = 'Platform Fees';
        $main_menu = 'platform-fees';
        $sub_menu = '';

        return view('admin.platform-fees.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function fetch(Request $request)
    {
        $results = PlatformFee::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('user_type', 'LIKE', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate();

        return view('admin.platform-fees._platform-fees', compact('results'))->render();
    }

    public function create()
    {
        $page = 'Add Platform Fee';
        $main_menu = 'platform-fees';
        $sub_menu = '';

        return view('admin.platform-fees.create', compact('page', 'main_menu', 'sub_menu'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_type' => 'required|in:user,driver',
            'fee_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
        ]);

        PlatformFee::where('user_type', $validated['user_type'])
            ->where('active', true)
            ->update(['active' => false]);

        PlatformFee::create($validated);

        return redirect('platform-fees')->with('success', 'Platform fee added successfully');
    }

    public function toggleStatus(PlatformFee $platformFee)
    {
        $platformFee->update([
            'active' => !$platformFee->active,
        ]);

        return redirect('platform-fees')->with('success', 'Platform fee status updated successfully');
    }
}
