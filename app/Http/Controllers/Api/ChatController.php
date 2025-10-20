<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // Placeholder: wire to OpenAI/local LLM later and use role-aware context
        $query = (string) $request->input('query', '');

        $log = \App\Models\ChatLog::create([
            'user_id' => optional($request->user())->id,
            'role' => optional($request->user())->role,
            'query' => $query,
            'response' => 'AI chat integration not yet configured. This endpoint will call OpenAI/local LLM with role-aware context and DB-backed answers.',
        ]);

        return response()->json([
            'id' => $log->id,
            'query' => $query,
            'response' => $log->response,
        ]);
    }
}
