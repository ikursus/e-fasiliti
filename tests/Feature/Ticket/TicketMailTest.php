<?php

namespace Tests\Feature\Ticket;

use App\Mail\TicketEventMail;
use App\Models\NotificationTemplate;

class TicketMailTest extends TicketTestCase
{
    public function test_the_mailable_renders_the_template_with_placeholders(): void
    {
        $template = NotificationTemplate::factory()->create([
            'key' => 'tiket.ujian',
            'channel' => 'emel',
            'locale' => 'ms',
            'subject' => 'Tiket {{nombor_tiket}} telah diterima',
            'body' => "Salam {{nama_pelapor}},\n\nTiket {{nombor_tiket}} kini dalam proses.",
            'placeholders' => ['nombor_tiket', 'nama_pelapor'],
        ]);

        $mail = new TicketEventMail($template, [
            'nombor_tiket' => 'TKT-202609-00001',
            'nama_pelapor' => 'Ahmad Zaki',
        ]);

        $this->assertSame('Tiket TKT-202609-00001 telah diterima', $mail->envelope()->subject);

        // Mailable::render() ialah API awam kelas asas; memanggilnya di sini
        // mengesan sebarang kaedah subkelas yang bertembung namanya.
        $html = (string) $mail->render();

        $this->assertStringContainsString('Tiket TKT-202609-00001 kini dalam proses.', $html);
        $this->assertStringNotContainsString('{{', $html);
    }

    public function test_unknown_placeholders_degrade_to_an_empty_string(): void
    {
        $template = NotificationTemplate::factory()->create([
            'subject' => 'Tiket {{nombor_tiket}} — {{medan_tiada}}',
            'body' => 'Kandungan.',
            'placeholders' => ['nombor_tiket', 'medan_tiada'],
        ]);

        $mail = new TicketEventMail($template, ['nombor_tiket' => 'TKT-202609-00002']);

        $this->assertSame('Tiket TKT-202609-00002 — ', $mail->envelope()->subject);
    }
}
