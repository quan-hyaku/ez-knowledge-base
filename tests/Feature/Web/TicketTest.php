<?php

namespace EzKnowledgeBase\Tests\Feature\Web;

use EzKnowledgeBase\Models\KbTicket;
use EzKnowledgeBase\Tests\TestCase;

class TicketTest extends TestCase
{
    public function test_ticket_form_returns_200(): void
    {
        $response = $this->get('/help-center/ticket');

        $response->assertStatus(200);
    }

    public function test_valid_ticket_submission_creates_ticket_and_redirects(): void
    {
        $response = $this->post('/help-center/ticket', [
            'subject' => 'Need help with login',
            'description' => 'I cannot log in to my account.',
            'category' => 'account',
            'urgency' => 'high',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kb_tickets', [
            'subject' => 'Need help with login',
            'email' => 'john@example.com',
        ]);
    }

    public function test_missing_required_fields_fails_validation(): void
    {
        $response = $this->post('/help-center/ticket', [
            'subject' => '',
            'description' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['subject', 'description', 'name', 'email']);
    }

    public function test_email_validation_works(): void
    {
        $response = $this->post('/help-center/ticket', [
            'subject' => 'Test',
            'description' => 'Test description',
            'name' => 'Test User',
            'email' => 'not-an-email',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }
}
