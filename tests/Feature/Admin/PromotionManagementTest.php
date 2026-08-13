<?php

use App\Livewire\Admin\PromotionManagement;
use App\Models\Promotion;
use App\Models\User;
use Livewire\Livewire;

test('admin can create a promotion without errors', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    $response = Livewire::test(PromotionManagement::class)
        ->set('code', 'PROMO1234')
        ->set('title', 'Promo Test')
        ->set('description', 'Deskripsi test')
        ->set('type', 'percentage')
        ->set('discountValue', '10')
        ->set('maxDiscountAmount', '50000')
        ->set('minPurchaseAmount', '100000')
        ->set('usageLimit', '5')
        ->set('perUserLimit', '1')
        ->call('save');

    $response->assertHasNoErrors();
    expect(Promotion::where('code', 'PROMO1234')->exists())->toBeTrue();
});
