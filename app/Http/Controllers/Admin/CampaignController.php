<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['creator']);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            });
        }

        $campaigns = $query->latest()->paginate(15);

        // Analytics
        $analytics = [
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'scheduled_campaigns' => Campaign::where('status', 'scheduled')->count(),
            'completed_campaigns' => Campaign::where('status', 'completed')->count(),
            'total_budget' => Campaign::sum('budget'),
            'total_spent' => Campaign::sum('spent_budget'),
            'type_distribution' => Campaign::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'analytics'));
    }

    public function create()
    {
        return view('admin.campaigns.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Campaign::TYPES)),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'target_audience' => 'nullable|array',
            'campaign_data' => 'nullable|array',
        ]);

        Campaign::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'status' => 'draft',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'budget' => $request->budget,
            'spent_budget' => 0,
            'target_audience' => $request->target_audience ?? [],
            'campaign_data' => $this->processCampaignData($request->type, $request->campaign_data ?? []),
            'metrics' => [],
            'created_by' => 1, // Default admin user
        ]);

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['creator']);
        
        // Calculate performance metrics
        $metrics = $this->calculateCampaignMetrics($campaign);
        
        return view('admin.campaigns.show', compact('campaign', 'metrics'));
    }

    public function edit(Campaign $campaign)
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Campaign::TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'target_audience' => 'nullable|array',
            'campaign_data' => 'nullable|array',
        ]);

        $campaign->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'budget' => $request->budget,
            'target_audience' => $request->target_audience ?? [],
            'campaign_data' => $this->processCampaignData($request->type, $request->campaign_data ?? []),
        ]);

        return redirect()->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    public function activate(Campaign $campaign)
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return back()->with('error', 'Only draft or scheduled campaigns can be activated.');
        }

        $campaign->update(['status' => 'active']);
        
        return back()->with('success', 'Campaign activated successfully.');
    }

    public function pause(Campaign $campaign)
    {
        if ($campaign->status !== 'active') {
            return back()->with('error', 'Only active campaigns can be paused.');
        }

        $campaign->update(['status' => 'paused']);
        
        return back()->with('success', 'Campaign paused successfully.');
    }

    public function resume(Campaign $campaign)
    {
        if ($campaign->status !== 'paused') {
            return back()->with('error', 'Only paused campaigns can be resumed.');
        }

        $campaign->update(['status' => 'active']);
        
        return back()->with('success', 'Campaign resumed successfully.');
    }

    public function complete(Campaign $campaign)
    {
        $campaign->update(['status' => 'completed']);
        
        return back()->with('success', 'Campaign marked as completed.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->status === 'active') {
            return back()->with('error', 'Cannot delete an active campaign. Please pause or complete it first.');
        }

        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    public function analytics()
    {
        $analytics = [
            'overview' => [
                'total_campaigns' => Campaign::count(),
                'active_campaigns' => Campaign::where('status', 'active')->count(),
                'completed_campaigns' => Campaign::where('status', 'completed')->count(),
                'total_budget' => Campaign::sum('budget'),
                'total_spent' => Campaign::sum('spent_budget'),
                'average_budget' => Campaign::avg('budget'),
            ],
            'performance' => [
                'budget_utilization' => Campaign::selectRaw('AVG((spent_budget / NULLIF(budget, 0)) * 100) as percentage')->value('percentage') ?? 0,
                'top_performing' => Campaign::where('status', 'completed')
                    ->orderByRaw('(spent_budget / NULLIF(budget, 0)) DESC')
                    ->limit(5)
                    ->get(),
                'type_performance' => Campaign::selectRaw('type, COUNT(*) as count, AVG(budget) as avg_budget, SUM(spent_budget) as total_spent')
                    ->groupBy('type')
                    ->get(),
            ],
            'recent_activity' => Campaign::with('creator')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('admin.campaigns.analytics', compact('analytics'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,pause,complete,delete',
            'campaigns' => 'required|array',
            'campaigns.*' => 'exists:campaigns,id'
        ]);

        $campaigns = Campaign::whereIn('id', $request->campaigns);

        switch ($request->action) {
            case 'activate':
                $campaigns->whereIn('status', ['draft', 'scheduled', 'paused'])->update(['status' => 'active']);
                $message = 'Campaigns activated successfully.';
                break;
            case 'pause':
                $campaigns->where('status', 'active')->update(['status' => 'paused']);
                $message = 'Campaigns paused successfully.';
                break;
            case 'complete':
                $campaigns->whereIn('status', ['active', 'paused'])->update(['status' => 'completed']);
                $message = 'Campaigns marked as completed.';
                break;
            case 'delete':
                $campaigns->where('status', '!=', 'active')->delete();
                $message = 'Campaigns deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }

    private function processCampaignData($type, $data)
    {
        // Process campaign-specific data based on type
        switch ($type) {
            case 'email':
                return [
                    'subject' => $data['subject'] ?? '',
                    'template' => $data['template'] ?? '',
                    'sender_name' => $data['sender_name'] ?? '',
                    'reply_to' => $data['reply_to'] ?? '',
                ];
            case 'banner':
                return [
                    'image_url' => $data['image_url'] ?? '',
                    'click_url' => $data['click_url'] ?? '',
                    'placement' => $data['placement'] ?? 'homepage',
                ];
            case 'discount':
                return [
                    'discount_percentage' => $data['discount_percentage'] ?? 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'minimum_amount' => $data['minimum_amount'] ?? 0,
                    'product_categories' => $data['product_categories'] ?? [],
                ];
            default:
                return $data;
        }
    }

    private function calculateCampaignMetrics($campaign)
    {
        // This would typically calculate real metrics based on campaign performance
        // For now, we'll return sample metrics
        return [
            'impressions' => rand(1000, 50000),
            'clicks' => rand(50, 2000),
            'conversions' => rand(5, 200),
            'click_rate' => rand(2, 15) . '%',
            'conversion_rate' => rand(1, 10) . '%',
            'cost_per_click' => 'Rp ' . number_format(rand(500, 5000)),
            'return_on_investment' => rand(150, 400) . '%',
        ];
    }
}
