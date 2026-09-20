<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    /**
     * Add loyalty points to a user.
     */
    public function addPoints(User $user, int $amount, string $description)
    {
        return DB::transaction(function () use ($user, $amount, $description) {
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $loyaltyPoint->increment('balance', $amount);

            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'earned',
                'description' => $description,
            ]);

            return $loyaltyPoint;
        });
    }

    /**
     * Deduct loyalty points from a user.
     */
    public function spendPoints(User $user, int $amount, string $description)
    {
        return DB::transaction(function () use ($user, $amount, $description) {
            $loyaltyPoint = LoyaltyPoint::where('user_id', $user->id)->first();

            if (!$loyaltyPoint || $loyaltyPoint->balance < $amount) {
                throw new \Exception('Points insuffisants.');
            }

            $loyaltyPoint->decrement('balance', $amount);

            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => 'spent',
                'description' => $description,
            ]);

            return $loyaltyPoint;
        });
    }

    /**
     * Generate a referral code for a user.
     */
    public function generateReferralCode(User $user)
    {
        $code = strtoupper(Str::random(8));

        return Referral::create([
            'referrer_id' => $user->id,
            'referral_code' => $code,
            'rewarded' => false,
        ]);
    }

    /**
     * Apply a referral code and reward the referrer.
     */
    public function applyReferral(string $code, User $referredUser)
    {
        return DB::transaction(function () use ($code, $referredUser) {
            $referral = Referral::where('referral_code', $code)->first();

            if (!$referral) {
                throw new \Exception('Code de parrainage invalide.');
            }

            if ($referral->rewarded) {
                throw new \Exception('Ce code a déjà été utilisé.');
            }

            // Update referral
            $referral->update([
                'referred_id' => $referredUser->id,
                'rewarded' => true,
            ]);

            // Reward the referrer (e.g., 500 points)
            $this->addPoints($referredUser->user, 500, 'Bonus de parrainage pour avoir invité ' . $referredUser->name);

            return $referral;
        });
    }
}
