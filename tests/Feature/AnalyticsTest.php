<?php

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);
});

function analyticsUser(string $role = 'user'): User
{
    $user = User::factory()->create([
        'is_active' => true,
        'password' => Hash::make('password'),
    ]);
    $user->assignRole($role);

    return $user;
}

function analyticsDocument(User $user, string $type = Document::TYPE_SOA, string $recipient = 'Acme', float $unitCost = 100): Document
{
    $document = Document::create([
        'user_id' => $user->id,
        'type' => $type,
        'control_number' => strtoupper(substr($type, 0, 1)).'-'.fake()->unique()->numberBetween(1000, 9999),
        'recipient_name' => $recipient,
        'document_date' => now()->toDateString(),
        'discount' => 10,
        'status' => Document::STATUS_FINALIZED,
    ]);
    $document->items()->create(['item_number' => 1, 'name' => 'Service', 'description' => 'Service', 'quantity' => 2, 'unit_cost' => $unitCost]);
    $document->refresh();

    return $document;
}

it('redirects guests from analytics', function () {
    $this->get(route('analytics.index'))->assertRedirect(route('login'));
});

it('scopes a user report to that users documents and omits people data', function () {
    $user = analyticsUser();
    $other = analyticsUser();
    analyticsDocument($user, recipient: 'Own Client');
    analyticsDocument($other, recipient: 'Other Client');

    $response = $this->actingAs($user)->get(route('analytics.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Analytics/Index')
        ->where('kpis.total_documents', 1)
        ->where('kpis.total_billed', 190)
        ->where('clients.0.name', 'own client')
        ->where('people', null)
        ->where('users', null));
});

it('allows admins to see system totals and user breakdowns', function () {
    $admin = analyticsUser('admin');
    $user = analyticsUser();
    analyticsDocument($admin);
    analyticsDocument($user, Document::TYPE_QUOTATION);

    $response = $this->actingAs($admin)->get(route('analytics.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->where('kpis.total_documents', 2)
        ->where('people.leaderboard', fn ($rows) => count($rows) === 2)
        ->where('users', fn ($users) => count($users) === 2));
});

it('exports only the authenticated users documents', function () {
    $user = analyticsUser();
    $other = analyticsUser();
    analyticsDocument($user, recipient: 'Included Client');
    analyticsDocument($other, recipient: 'Excluded Client');

    $response = $this->actingAs($user)->get(route('analytics.export'));

    $response->assertOk();
    expect($response->streamedContent())->toContain('Included Client')->not->toContain('Excluded Client');
});
