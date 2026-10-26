<?php

declare(strict_types=1);

test('unknown email returns 404', function () {
    $this->get('/emails/bogus')->assertNotFound();
});

test('known email renders without any user of its own', function () {
    // The emails are previews now: the greeting name is hard coded into the
    // mail class, so no model has to stand behind them.
    $this->get('/emails/welcome')->assertOk();
});
