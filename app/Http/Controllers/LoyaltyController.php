<?php

namespace App\Http\Controllers;

use App\Services\LoyaltyService;
use App\Models\LoyaltyPoint;
use App\Models\Referral;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoyaltyController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    public function index()
    {
        $user = auth()->user();
        $points = LoyaltyPoint::where('user_id', $user->id)->first();
        $referral = Referral::where('referrer_id', $user->id)->first();

        return Inertia::render('Loyalty/Index', [
            'points' => $points ? $points->balance : 0,
            'referral_code' => $referral ? $referral->referral_code : null,
        ]);
    }

    public function generateCode()
    {
        $user = auth()->user();
        $referral = $this->loyaltyService->generateReferralCode($user);

        return response()->json([
            'code' => $referral->referral_code,
        ]);
    }

    public function applyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $this->loyaltyService->applyReferral($request->code, auth()->user());
            return response()->json(['message' => 'Code appliqué avec succès ! Vous avez reçu vos points.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
