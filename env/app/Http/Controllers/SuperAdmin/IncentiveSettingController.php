<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\incentive_settings;
use Illuminate\Http\Request;

class IncentiveSettingController extends Controller
{
  public function index()
    {
        $settings = incentive_settings::all();
        return view('superadmin.incentive_settings.index', compact('settings'));
    }

    public function create()
    {
        return view('superadmin.incentive_settings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'rank' => 'required|string|max:255',
            'required_ctp' => 'required|numeric|min:0',
            'min_lesser_leg_percent' => 'required|numeric|min:0|max:100',
            'required_downline_count' => 'required|integer|min:0',
            'required_downline_rank' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        incentive_settings::create([
            'rank' => $request->rank,
            'required_ctp' => $request->required_ctp,
            'min_lesser_leg_percent' => $request->min_lesser_leg_percent,
            'required_downline_count' => $request->required_downline_count,
            'required_downline_rank' => $request->required_downline_rank,
            'is_active' => $request->is_active ?? 0,
        ]);

        return redirect()->route('incentive_settings.index')
            ->with('success', 'Incentive setting created successfully.');
    }

    public function edit($id)
    {
        $setting = incentive_settings::findOrFail($id);
        return view('superadmin.incentive_settings.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rank' => 'required|string|max:255',
            'required_ctp' => 'required|numeric|min:0',
            'min_lesser_leg_percent' => 'required|numeric|min:0|max:100',
            'required_downline_count' => 'required|integer|min:0',
            'required_downline_rank' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $setting = incentive_settings::findOrFail($id);
        $setting->update([
            'rank' => $request->rank,
            'required_ctp' => $request->required_ctp,
            'min_lesser_leg_percent' => $request->min_lesser_leg_percent,
            'required_downline_count' => $request->required_downline_count,
            'required_downline_rank' => $request->required_downline_rank,
            'is_active' => $request->is_active ?? 0,
        ]);

        return redirect()->route('incentive_settings.index')
            ->with('success', 'Incentive setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = incentive_settings::findOrFail($id);
        $setting->delete();

        return redirect()->route('incentive_settings.index')
            ->with('success', 'Incentive setting deleted successfully.');
    }
}
