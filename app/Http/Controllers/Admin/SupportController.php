<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedAgent']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('subject', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q2) use ($request) {
                      $q2->where('name', 'LIKE', '%' . $request->search . '%')
                         ->orWhere('email', 'LIKE', '%' . $request->search . '%');
                  });
            });
        }

        $tickets = $query->latest()->paginate(20);

        // Get support agents
        $agents = User::where('is_admin', true)->get();

        // Analytics
        $analytics = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::open()->count(),
            'resolved_tickets' => SupportTicket::where('status', 'resolved')->count(),
            'average_resolution_time' => SupportTicket::where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours'),
            'priority_distribution' => SupportTicket::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
        ];

        return view('admin.support.index', compact('tickets', 'agents', 'analytics'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignedAgent', 'messages.user']);
        $agents = User::where('is_admin', true)->get();
        
        return view('admin.support.show', compact('ticket', 'agents'));
    }

    public function create()
    {
        $users = User::where('is_admin', false)->get();
        return view('admin.support.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(SupportTicket::CATEGORIES)),
            'priority' => 'required|in:' . implode(',', array_keys(SupportTicket::PRIORITIES)),
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user_id,
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'tags' => $request->tags ? array_map('trim', explode(',', $request->tags)) : null,
        ]);

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    public function edit(SupportTicket $ticket)
    {
        $users = User::where('is_admin', false)->get();
        $agents = User::where('is_admin', true)->get();
        
        return view('admin.support.edit', compact('ticket', 'users', 'agents'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(SupportTicket::CATEGORIES)),
            'priority' => 'required|in:' . implode(',', array_keys(SupportTicket::PRIORITIES)),
            'status' => 'required|in:' . implode(',', array_keys(SupportTicket::STATUSES)),
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|string',
        ]);

        $updateData = [
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => $request->status,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'tags' => $request->tags ? array_map('trim', explode(',', $request->tags)) : null,
        ];

        // Set resolved_at when status changes to resolved
        if ($request->status === 'resolved' && $ticket->status !== 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Support ticket updated successfully.');
    }

    public function addMessage(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'is_internal_note' => 'boolean'
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => 1, // Default admin user for now
            'message' => $request->message,
            'is_internal_note' => $request->boolean('is_internal_note', false),
        ]);

        // Update ticket status if needed
        if ($ticket->status === 'waiting_customer') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Message added successfully.');
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $ticket->update(['assigned_to' => $request->assigned_to]);

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function changeStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(SupportTicket::STATUSES))
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'resolved' && $ticket->status !== 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return back()->with('success', 'Ticket status updated successfully.');
    }

    public function destroy(SupportTicket $ticket)
    {
        $ticket->messages()->delete();
        $ticket->delete();

        return redirect()->route('admin.support.index')
            ->with('success', 'Support ticket deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:assign,change_status,delete',
            'tickets' => 'required|array',
            'tickets.*' => 'exists:support_tickets,id',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
            'status' => 'required_if:action,change_status|in:' . implode(',', array_keys(SupportTicket::STATUSES)),
        ]);

        $tickets = SupportTicket::whereIn('id', $request->tickets);

        switch ($request->action) {
            case 'assign':
                $tickets->update(['assigned_to' => $request->assigned_to]);
                $message = 'Tickets assigned successfully.';
                break;
            case 'change_status':
                $updateData = ['status' => $request->status];
                if ($request->status === 'resolved') {
                    $updateData['resolved_at'] = now();
                }
                $tickets->update($updateData);
                $message = 'Tickets status updated successfully.';
                break;
            case 'delete':
                $ticketIds = $request->tickets;
                SupportMessage::whereIn('ticket_id', $ticketIds)->delete();
                $tickets->delete();
                $message = 'Tickets deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}
