<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Re: [Ticket #{{ $ticket->id }}] {{ $ticket->subject }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f4f4f4; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;">
    <!-- Preheader text (hidden) -->
    <div style="display: none; max-height: 0px; overflow: hidden;">
        New reply on your support ticket #{{ $ticket->id }}: {{ $ticket->subject }}
    </div>

    <!-- Full-width wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 30px 15px;">

                <!-- Email container -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden;">

                    <!-- Logo Section -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <a href="{{ config('app.url') }}" target="_blank" style="text-decoration: none;">
                                <img src="{{ config('kb.logo.url') ?: asset('vendor/kb/KB-logo.png') }}" alt="{{ config('kb.logo.alt', 'Support') }}" width="180" style="display: block; width: 180px; max-width: 100%; height: auto;" />
                            </a>
                        </td>
                    </tr>

                    <!-- Heading -->
                    <tr>
                        <td align="center" style="padding: 10px 40px 5px 40px;">
                            <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #1a1a2e; line-height: 1.3;">
                                Support Reply
                            </h1>
                        </td>
                    </tr>

                    <!-- Ticket Badge -->
                    <tr>
                        <td align="center" style="padding: 5px 40px 20px 40px;">
                            <span style="display: inline-block; padding: 4px 14px; font-size: 13px; font-weight: 600; color: #0EA5E9; background-color: #f0f9ff; border-radius: 20px; border: 1px solid #bae6fd;">
                                Ticket #{{ $ticket->id }}
                            </span>
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td align="left" style="padding: 10px 40px 15px 40px;">
                            <p style="margin: 0; font-size: 15px; color: #4a4a4a; line-height: 1.6;">
                                Hi {{ $ticket->name ?? 'there' }},
                            </p>
                        </td>
                    </tr>

                    <!-- Context -->
                    <tr>
                        <td align="left" style="padding: 0 40px 10px 40px;">
                            <p style="margin: 0; font-size: 15px; color: #4a4a4a; line-height: 1.6;">
                                Our team has replied to your ticket: <strong>{{ $ticket->subject }}</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Reply Body -->
                    <tr>
                        <td align="center" style="padding: 5px 40px 25px 40px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0f9ff; border-radius: 6px; border-left: 4px solid #0EA5E9;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 600; color: #0EA5E9; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Reply
                                        </p>
                                        <p style="margin: 0; font-size: 14px; color: #4a4a4a; line-height: 1.6;">
                                            {!! nl2br(e($reply->body)) !!}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if($replyToAddress)
                    <!-- Reply Instruction -->
                    <tr>
                        <td align="left" style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.6; font-style: italic;">
                                You can reply directly to this email to respond to the ticket.
                            </p>
                        </td>
                    </tr>
                    @endif

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 40px;">
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0;" />
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 25px 40px 15px 40px;">
                            <p style="margin: 0; font-size: 13px; color: #9ca3af; line-height: 1.6;">
                                &copy; {{ date('Y') }} {{ config('kb.brand.copyright') }}
                            </p>
                            @if(config('kb.brand.address'))
                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #9ca3af; line-height: 1.5;">
                                {{ config('kb.brand.address') }}
                            </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 5px 40px 30px 40px;">
                            <a href="{{ config('app.url') }}" target="_blank" style="font-size: 12px; color: #0EA5E9; text-decoration: underline;">
                                {{ config('kb.support.website') }}
                            </a>
                            &nbsp;&bull;&nbsp;
                            <a href="mailto:{{ config('kb.support.email') }}" style="font-size: 12px; color: #0EA5E9; text-decoration: underline;">
                                {{ config('kb.support.email') }}
                            </a>
                        </td>
                    </tr>

                </table>
                <!-- End email container -->

            </td>
        </tr>
    </table>
</body>
</html>
