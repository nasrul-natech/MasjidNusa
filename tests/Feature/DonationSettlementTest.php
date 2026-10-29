<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_increments_once_and_ignores_a_later_call(): void
    {
        $donation = $this->donation(Donation::STATUS_WAITING_VERIFICATION);

        $this->assertTrue($donation->settle(Donation::STATUS_VERIFIED, [Donation::STATUS_WAITING_VERIFICATION]));
        $this->assertFalse($donation->fresh()->settle(Donation::STATUS_VERIFIED, [Donation::STATUS_WAITING_VERIFICATION]));

        $this->assertSame(10000, $donation->campaign->fresh()->collected_amount);
        $this->assertSame(Donation::STATUS_VERIFIED, $donation->fresh()->status);
    }

    public function test_rejected_donation_cannot_be_approved_afterwards(): void
    {
        $donation = $this->donation(Donation::STATUS_WAITING_VERIFICATION);

        $this->assertTrue($donation->closeIfOpen(Donation::STATUS_REJECTED, [
            Donation::STATUS_PENDING,
            Donation::STATUS_WAITING_VERIFICATION,
        ]));
        $this->assertFalse($donation->fresh()->settle(Donation::STATUS_VERIFIED, [Donation::STATUS_WAITING_VERIFICATION]));

        $this->assertSame(0, $donation->campaign->fresh()->collected_amount);
        $this->assertSame(Donation::STATUS_REJECTED, $donation->fresh()->status);
    }

    public function test_paid_donation_is_not_reopened_by_a_waiting_transition(): void
    {
        $donation = $this->donation(Donation::STATUS_PENDING);

        $this->assertTrue($donation->settle(Donation::STATUS_PAID, [Donation::STATUS_PENDING]));
        $this->assertFalse($donation->fresh()->settle(Donation::STATUS_PAID, [Donation::STATUS_PENDING, Donation::STATUS_WAITING_VERIFICATION]));

        $this->assertSame(10000, $donation->campaign->fresh()->collected_amount);
        $this->assertSame(Donation::STATUS_PAID, $donation->fresh()->status);
    }

    public function test_logged_in_html_is_not_stored_by_the_browser(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $guest = $this->get('/login');
        $guest->assertOk();
        $this->assertStringNotContainsString('no-store', (string) $guest->headers->get('Cache-Control'));

        $page = $this->actingAs($user)->get(route('user.profile'));
        $page->assertOk();
        $this->assertStringContainsString('no-store', (string) $page->headers->get('Cache-Control'));
    }

    private function donation(string $status): Donation
    {
        $user = User::factory()->create();
        $campaign = DonationCampaign::create([
            'title' => 'Infak',
            'slug' => 'infak-'.uniqid(),
            'target_amount' => 100000,
            'collected_amount' => 0,
            'status' => 'active',
        ]);

        return Donation::create([
            'donation_code' => 'DON-'.strtoupper(uniqid()),
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'amount' => 10000,
            'donor_name' => $user->name,
            'donor_email' => $user->email,
            'status' => $status,
            'payment_method_type' => 'manual',
        ]);
    }
}
