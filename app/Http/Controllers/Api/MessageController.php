<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        // Optional filter by order_id
        $query = Message::query();
        if (request()->filled('order_id')) {
            $query->where('order_id', request('order_id'));
        } else {
            $query->where(function($q){
                $q->where('sender_id', Auth::id())->orWhere('receiver_id', Auth::id());
            });
        }
        return $query->orderBy('created_at', 'asc')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'is_admin' => 'boolean',
            'order_id' => 'nullable|exists:orders,id',
        ]);
        $msg = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_admin' => $request->input('is_admin', false),
            'order_id' => $request->input('order_id'),
        ]);
        return response()->json($msg, 201);
    }
}
