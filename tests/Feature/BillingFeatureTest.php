<?php

it('redirects guests when accessing billing index', function () {
    $response = $this->get(route('billing.index'));

    $response->assertRedirect('/login');
});

it('registers factura.com billing routes', function () {
    $stampUrl = route('billing.facturacom.stamp', ['billing' => 1]);
    $syncUrl = route('billing.facturacom.sync', ['billing' => 1]);

    expect($stampUrl)->toContain('/billing/1/facturacom/stamp');
    expect($syncUrl)->toContain('/billing/1/facturacom/sync');
});
