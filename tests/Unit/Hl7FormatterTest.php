<?php

it('can decode text', function () {
    expect(new \mmerlijn\msgHl7\helpers\Hl7Text('\\T\\')->decode())->toBe('&');
});
