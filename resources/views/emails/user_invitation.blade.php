<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    {{-- <title>{{$details['title']}}</title> --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0;">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">

    <thead>

    </thead>
    <tbody>
    <tr>
        <td align="center">
{{--            <img src="https://taxpages.pk/store/logo.png"/>--}}
        </td>
    </tr>
    <tr>
        <td style="text-align:center;margin-top:10px;margin-bottom:10px">
            <p>Dear <strong>{{$sendingDetails['sender_name'] ?? ''}}</strong>, You've been invited to {{ $sendingDetails['business'] }} Teams as a {{ $sendingDetails['role'] }}.</p>
        </td>
    </tr>
    <tr>
        <td><p>&nbsp;</p>
    </tr>
    <tr style="text-align:center">
    </tr>
    <tr style="text-align:center">
        <td>
            <p>&nbsp;</p>
            <p>You are receiving this email from {{env('APP_NAME')}}
            </p>
            <p>Xribute respects your privacy. Review our Online Services Privacy Statement.
            </p>
            <p>Sent from Xribute
            </p>
            <p>
                
            </p>

        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
