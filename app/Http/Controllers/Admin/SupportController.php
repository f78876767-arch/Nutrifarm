<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        return response('Support tickets index (placeholder)');
    }

    public function create()
    {
        return response('Support create (placeholder)');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Support ticket created (placeholder)');
    }

    public function show($id)
    {
        return response("Support show #{$id} (placeholder)");
    }

    public function edit($id)
    {
        return response("Support edit #{$id} (placeholder)");
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Support ticket updated (placeholder)');
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Support ticket deleted (placeholder)');
    }

    // Extra endpoints referenced in routes
    public function bulkAction(Request $request)
    {
        return redirect()->back()->with('success', 'Support bulk action queued (placeholder)');
    }

    public function assign($ticket)
    {
        return redirect()->back()->with('success', "Ticket {$ticket} assigned (placeholder)");
    }

    public function changeStatus($ticket)
    {
        return redirect()->back()->with('success', "Ticket {$ticket} status changed (placeholder)");
    }

    public function addMessage($ticket, Request $request)
    {
        return redirect()->back()->with('success', "Message added to ticket {$ticket} (placeholder)");
    }
}
