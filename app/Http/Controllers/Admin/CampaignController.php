<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    // List campaigns
    public function index()
    {
        return response('Campaigns index (placeholder)');
    }

    // Show create form
    public function create()
    {
        return response('Campaign create (placeholder)');
    }

    // Store new campaign
    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Campaign created (placeholder)');
    }

    // Show one campaign
    public function show($id)
    {
        return response("Campaign show #{$id} (placeholder)");
    }

    // Edit form
    public function edit($id)
    {
        return response("Campaign edit #{$id} (placeholder)");
    }

    // Update campaign
    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Campaign updated (placeholder)');
    }

    // Delete campaign
    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Campaign deleted (placeholder)');
    }

    // Extra endpoints referenced in routes
    public function analytics()
    {
        return response('Campaigns analytics (placeholder)');
    }

    public function bulkAction(Request $request)
    {
        return redirect()->back()->with('success', 'Bulk action queued (placeholder)');
    }

    public function activate($id)
    {
        return redirect()->back()->with('success', "Campaign {$id} activated (placeholder)");
    }

    public function pause($id)
    {
        return redirect()->back()->with('success', "Campaign {$id} paused (placeholder)");
    }

    public function resume($id)
    {
        return redirect()->back()->with('success', "Campaign {$id} resumed (placeholder)");
    }

    public function complete($id)
    {
        return redirect()->back()->with('success', "Campaign {$id} completed (placeholder)");
    }
}
