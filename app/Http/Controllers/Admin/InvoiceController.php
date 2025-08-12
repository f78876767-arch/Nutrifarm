<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $orders = Order::with('user')->orderBy('created_at', 'desc')->get();
        
        return view('admin.invoices.create', compact('users', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'external_id' => 'required|string|max:255|unique:invoices',
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payer_email' => 'nullable|email',
            'description' => 'nullable|string|max:500',
            'currency' => 'required|string|max:3',
            'expiry_date' => 'nullable|date|after:now',
        ]);

        Invoice::create($request->all());

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice created successfully');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'order']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $users = User::orderBy('name')->get();
        $orders = Order::with('user')->orderBy('created_at', 'desc')->get();
        
        return view('admin.invoices.edit', compact('invoice', 'users', 'orders'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'external_id' => 'required|string|max:255|unique:invoices,external_id,' . $invoice->id,
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payer_email' => 'nullable|email',
            'description' => 'nullable|string|max:500',
            'currency' => 'required|string|max:3',
            'expiry_date' => 'nullable|date|after:now',
            'status' => 'required|in:PENDING,PAID,SETTLED,EXPIRED',
        ]);

        $invoice->update($request->all());

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice updated successfully');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'PAID') {
            return redirect()->route('admin.invoices.index')
                ->with('error', 'Cannot delete paid invoice');
        }

        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully');
    }
}
