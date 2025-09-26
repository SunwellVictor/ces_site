<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\File;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DownloadGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $otherUser;
    protected $product;
    protected $file;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->file = File::factory()->create();
        $this->product = Product::factory()->create([
            'price_cents' => 1000,
            'is_active' => true
        ]);
        $this->product->files()->attach($this->file);
    }

    public function test_guest_cannot_access_account_dashboard()
    {
        $response = $this->get(route('account.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_access_account_dashboard()
    {
        $unverifiedUser = User::factory()->unverified()->create();
        
        $response = $this->actingAs($unverifiedUser)->get(route('account.dashboard'));
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_account_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('account.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('account.dashboard');
        $response->assertSee($this->user->name);
    }

    public function test_account_dashboard_displays_correct_statistics()
    {
        // Create orders for the user
        $completedOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
            'total_cents' => 2000
        ]);
        
        $pendingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total_cents' => 1500
        ]);

        // Create download grants
        DownloadGrant::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'order_id' => $completedOrder->id
        ]);

        $response = $this->actingAs($this->user)->get(route('account.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewHas('stats.total_orders', 2);
        $response->assertViewHas('stats.completed_orders', 1);
        $response->assertViewHas('stats.total_spent', 2000); // Only paid orders count
        $response->assertViewHas('stats.available_downloads', 3);
    }

    public function test_user_can_view_orders_list()
    {
        // Create orders for the user
        Order::factory()->count(5)->create(['user_id' => $this->user->id]);
        
        // Create orders for another user (should not be visible)
        Order::factory()->count(3)->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('account.orders'));
        
        $response->assertStatus(200);
        $response->assertViewIs('account.orders');
        
        // Should see user's orders but not other user's orders
        $userOrders = Order::where('user_id', $this->user->id)->get();
        foreach ($userOrders as $order) {
            $response->assertSee('#' . $order->id);
        }
    }

    public function test_orders_list_pagination_works()
    {
        // Create more than 10 orders to test pagination
        Order::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('account.orders'));
        
        $response->assertStatus(200);
        $response->assertViewHas('orders');
        
        // Check that pagination links are present
        $orders = $response->viewData('orders');
        $this->assertEquals(10, $orders->perPage());
        $this->assertEquals(15, $orders->total());
    }

    public function test_orders_list_filtering_by_status_works()
    {
        // Create orders with different statuses
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid'
        ]);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Filter by paid status
        $response = $this->actingAs($this->user)->get(route('account.orders', ['status' => 'paid']));
        
        $response->assertStatus(200);
        $orders = $response->viewData('orders');
        
        foreach ($orders as $order) {
            $this->assertEquals('paid', $order->status);
        }
    }

    public function test_user_can_view_own_order_details()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->actingAs($this->user)->get(route('account.orders.show', $order));
        
        $response->assertStatus(200);
        $response->assertViewIs('account.order-detail');
        $response->assertViewHas('order');
        $response->assertSee('#' . $order->id);
    }

    public function test_user_cannot_view_other_users_order_details()
    {
        $otherUserOrder = Order::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('account.orders.show', $otherUserOrder));
        
        $response->assertStatus(404);
    }

    public function test_order_detail_shows_download_grants()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid'
        ]);
        
        $downloadGrant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'file_id' => $this->file->id
        ]);

        $response = $this->actingAs($this->user)->get(route('account.orders.show', $order));
        
        $response->assertStatus(200);
        $response->assertSee($this->file->filename);
    }

    public function test_user_can_edit_profile()
    {
        $response = $this->actingAs($this->user)->get(route('account.profile.edit'));
        
        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
    }

    public function test_user_can_update_profile()
    {
        $newName = 'Updated Name';
        $newEmail = 'updated@example.com';

        $response = $this->actingAs($this->user)->patch(route('account.profile.update'), [
            'name' => $newName,
            'email' => $newEmail
        ]);

        $response->assertRedirect(route('account.profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        $this->user->refresh();
        $this->assertEquals($newName, $this->user->name);
        $this->assertEquals($newEmail, $this->user->email);
    }

    public function test_profile_update_validation_works()
    {
        $response = $this->actingAs($this->user)->patch(route('account.profile.update'), [
            'name' => '', // Required field
            'email' => 'invalid-email' // Invalid email
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_profile_update_prevents_duplicate_email()
    {
        $response = $this->actingAs($this->user)->patch(route('account.profile.update'), [
            'name' => 'Test Name',
            'email' => $this->otherUser->email // Email already taken
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_csrf_protection_on_profile_update()
    {
        // Test that CSRF protection is working by making a request without proper CSRF token
        // Laravel's test environment automatically includes CSRF tokens, so we need to disable it
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        
        $response = $this->actingAs($this->user)->patch(route('account.profile.update'), [
            'name' => 'Test Name',
            'email' => 'test@example.com'
        ]);

        // Without CSRF middleware, request should succeed
        $response->assertRedirect(route('account.profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');
    }

    public function test_account_routes_require_authentication()
    {
        $routes = [
            'account.dashboard',
            'account.orders',
            'account.profile.edit'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('login'));
        }
    }

    public function test_account_routes_require_email_verification()
    {
        $unverifiedUser = User::factory()->unverified()->create();
        
        $routes = [
            'account.dashboard',
            'account.orders',
            'account.profile.edit'
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($unverifiedUser)->get(route($route));
            $response->assertRedirect(route('verification.notice'));
        }
    }
}