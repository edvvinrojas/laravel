<?php

use App\Console\Commands\UpdateOverdueInvoices;
use Illuminate\Support\Facades\Artisan;

it('registers the billing:mark-overdue command', function () {
    $commands = Artisan::all();
    expect($commands)->toHaveKey('billing:mark-overdue');
    expect($commands['billing:mark-overdue'])->toBeInstanceOf(UpdateOverdueInvoices::class);
});

it('exposes a route to edit a TI license', function () {
    $url = route('ti-equipment.licenses.edit', ['license' => 99]);
    expect($url)->toContain('/ti-licenses/99/edit');
});

it('exposes a route to update a TI license', function () {
    expect(route('ti-equipment.licenses.update', ['license' => 99]))
        ->toContain('/ti-licenses/99');
});

it('exposes a route to edit a TI peripheral', function () {
    $url = route('ti-equipment.peripherals.edit', ['tiEquipment' => 1, 'peripheral' => 2]);
    expect($url)->toContain('/ti-equipment/1/peripherals/2/edit');
});

it('exposes a route to update a TI peripheral', function () {
    $url = route('ti-equipment.peripherals.update', ['tiEquipment' => 1, 'peripheral' => 2]);
    expect($url)->toContain('/ti-equipment/1/peripherals/2');
});

it('redirects guests away from new edit routes', function () {
    $this->get(route('ti-equipment.licenses.edit', ['license' => 1]))
        ->assertRedirect('/login');
});
