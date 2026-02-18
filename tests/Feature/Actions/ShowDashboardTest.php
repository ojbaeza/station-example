<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use Tests\TestCase;

class ShowDashboardTest extends TestCase
{
    public function testShowDashboardReturnsSuccessfulResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testShowDashboardReturnsViewResponse(): void
    {
        $response = $this->get('/');

        $response->assertViewIs('dashboard');
    }
}
