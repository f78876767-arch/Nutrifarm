<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return response('Reviews index (placeholder)');
    }

    public function analytics()
    {
        return response('Reviews analytics (placeholder)');
    }

    public function bulkAction(Request $request)
    {
        return redirect()->back()->with('success', 'Review bulk action queued (placeholder)');
    }

    public function show($id)
    {
        return response("Review show #{$id} (placeholder)");
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Review deleted (placeholder)');
    }

    public function approve($id)
    {
        return redirect()->back()->with('success', "Review {$id} approved (placeholder)");
    }

    public function reject($id)
    {
        return redirect()->back()->with('success', "Review {$id} rejected (placeholder)");
    }

    public function respond($id)
    {
        return redirect()->back()->with('success', "Responded to review {$id} (placeholder)");
    }
}
