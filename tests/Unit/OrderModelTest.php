<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /** @test */
    public function order_has_many_items(): void
    {
        $order = Order::factory()->create();

        $this->assertCount(0, $order->items);

        // Create order items manually
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->fresh()->items);
    }

    /** @test */
    public function order_number_is_generated(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    /** @test */
    public function order_can_be_identified_as_cart_type(): void
    {
        $cartOrder = Order::factory()->create(['order_type' => 'cart']);
        $inquiryOrder = Order::factory()->create(['order_type' => 'inquiry']);

        $this->assertTrue($cartOrder->isCart());
        $this->assertFalse($cartOrder->isInquiry());
        $this->assertFalse($inquiryOrder->isCart());
        $this->assertTrue($inquiryOrder->isInquiry());
    }

    /** @test */
    public function order_total_is_formatted_correctly(): void
    {
        $order = Order::factory()->create(['total_amount' => 15000000]);

        $this->assertEquals('Rp 15.000.000', $order->formatted_total);
    }

    /** @test */
    public function order_type_label_is_correct(): void
    {
        $cartOrder = Order::factory()->make(['order_type' => 'cart']);
        $inquiryOrder = Order::factory()->make(['order_type' => 'inquiry']);

        $this->assertEquals('Pembelian', $cartOrder->order_type_label);
        $this->assertEquals('Booking/Inquiry', $inquiryOrder->order_type_label);
    }

    /** @test */
    public function order_can_be_cancelled_by_user_when_pending(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->assertTrue($order->canBeCancelledByUser());
    }

    /** @test */
    public function order_cannot_be_cancelled_when_paid(): void
    {
        $order = Order::factory()->create(['status' => 'paid']);

        $this->assertFalse($order->canBeCancelledByUser());
    }

    /** @test */
    public function order_scope_cart_filters_correctly(): void
    {
        Order::factory()->create(['order_type' => 'cart']);
        Order::factory()->create(['order_type' => 'cart']);
        Order::factory()->create(['order_type' => 'inquiry']);

        $cartOrders = Order::cart()->get();

        $this->assertCount(2, $cartOrders);
    }

    /** @test */
    public function order_scope_inquiry_filters_correctly(): void
    {
        Order::factory()->create(['order_type' => 'cart']);
        Order::factory()->create(['order_type' => 'inquiry']);
        Order::factory()->create(['order_type' => 'inquiry']);

        $inquiryOrders = Order::inquiry()->get();

        $this->assertCount(2, $inquiryOrders);
    }

    /** @test */
    public function order_scope_pending_booking_filters_correctly(): void
    {
        Order::factory()->create(['order_type' => 'inquiry', 'status' => 'pending']);
        Order::factory()->create(['order_type' => 'inquiry', 'status' => 'approved']);
        Order::factory()->create(['order_type' => 'cart', 'status' => 'pending']);

        $pendingBookings = Order::pendingBooking()->get();

        $this->assertCount(1, $pendingBookings);
    }

    /** @test */
    public function order_scope_active_filters_non_terminal(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'paid']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'completed']);
        Order::factory()->create(['status' => 'cancelled']);

        $activeOrders = Order::active()->get();

        $this->assertCount(3, $activeOrders);
    }

    /** @test */
    public function order_scope_completed_filters_terminal_complete(): void
    {
        Order::factory()->create(['status' => 'completed']);
        Order::factory()->create(['status' => 'delivered']);
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'cancelled']);

        $completedOrders = Order::completed()->get();

        $this->assertCount(2, $completedOrders);
    }

    /** @test */
    public function order_scope_user_orders_filters_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Order::factory()->create(['user_id' => $user1->id, 'order_type' => 'cart']);
        Order::factory()->create(['user_id' => $user1->id, 'order_type' => 'cart']);
        Order::factory()->create(['user_id' => $user2->id, 'order_type' => 'cart']);

        $user1Orders = Order::userOrders($user1->id)->get();

        $this->assertCount(2, $user1Orders);
    }

    /** @test */
    public function order_scope_user_bookings_filters_by_user_and_type(): void
    {
        $user = User::factory()->create();

        Order::factory()->create(['user_id' => $user->id, 'order_type' => 'inquiry']);
        Order::factory()->create(['user_id' => $user->id, 'order_type' => 'inquiry']);
        Order::factory()->create(['user_id' => $user->id, 'order_type' => 'cart']);

        $userBookings = Order::userBookings($user->id)->get();

        $this->assertCount(2, $userBookings);
    }

    /** @test */
    public function order_can_check_assignment(): void
    {
        $staff = User::factory()->create();
        $order = Order::factory()->create(['assigned_staff_id' => $staff->id]);

        $this->assertTrue($order->isAssigned());
        $this->assertTrue($order->isAssignedTo($staff->id));
        $this->assertFalse($order->isAssignedTo(999));
    }

    /** @test */
    public function order_generates_booking_code(): void
    {
        $code = Order::generateBookingCode();

        $this->assertStringStartsWith('BK-', $code);
    }
}
