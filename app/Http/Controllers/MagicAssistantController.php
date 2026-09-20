<?php

namespace App\Http\Controllers;

use App\Services\MagicAssistantService;
use Illuminate\Http\Request;

class MagicAssistantController extends Controller
{
    protected $assistantService;

    public function __construct(MagicAssistantService $assistantService)
    {
        $this->assistantService = $assistantService;
    }

    public function index()
    {
        return view('magic-assistant');
    }

    public function suggest(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $result = $this->assistantService->getSuggestions($request->prompt);

        return response()->json([
            'message' => $result['message'],
            'suggestions' => $result['suggestions']
        ]);
    }
}

