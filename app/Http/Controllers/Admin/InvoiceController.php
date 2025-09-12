<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return response('Invoices index (placeholder)');
    }

    public function create()
    {
        return response('Invoice create (placeholder)');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Invoice created (placeholder)');
    }

    public function show($id)
    {
        return response("Invoice show #{$id} (placeholder)");
    }

    public function edit($id)
    {
        return response("Invoice edit #{$id} (placeholder)");
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Invoice updated (placeholder)');
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Invoice deleted (placeholder)');
    }
}
