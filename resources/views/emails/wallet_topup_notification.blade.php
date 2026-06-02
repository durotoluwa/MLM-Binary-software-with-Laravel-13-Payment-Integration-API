 



<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title> DLT Health Plus</title>

  <!-- Google Fonts (Poppins) -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body style="Margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Poppins', Arial, sans-serif;">
  <center style="width: 100%; background-color: #f4f4f4;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: auto; background-color: #ffffff; font-family: 'Poppins', Arial, sans-serif;">
      
      <!-- Logo -->
      <tr>
        <td align="center" style="padding: 40px 0;">
          <!-- Replace with your logo -->
          <img src="https://devophost.com/dltlogo.png" alt="Company Logo" width="250"  style="display: block; margin: 0 auto;">
        </td>
      </tr>

      <!-- Heading -->
      <tr>
        <td style="padding: 20px; text-align: center;">
          <h2 style="margin: 0; font-size: 22px; color: #333;">New Wallet Top-up Submitted</h2>
        </td>
      </tr>
 
      <!-- User Details -->
      <tr>
        <td style="padding: 20px 40px; font-size: 16px; color: #555;">
<p>The following user submitted a wallet top-up request:</p>

<ul>
    <li><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</li>
    <li><strong>Email:</strong> {{ $user->email }}</li>
    <li><strong>Amount:</strong> ₦{{ number_format($topup->amount, 2) }}</li>
    <li><strong>Bank Name:</strong> {{ $topup->bank_name }}</li>
    <li><strong>Account Name:</strong> {{ $topup->account_name }}</li>
    <li><strong>Method:</strong> Bank Transfer</li>
    <li><strong>Submitted At:</strong> {{ $topup->created_at }}</li>
</ul>

@if($topup->payment_proof)
    <p><strong>Proof of Payment:</strong></p>
    <p>
        <a href="{{ asset($topup->payment_proof) }}" target="_blank">
            View Proof
        </a>
    </p>
@endif
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td align="center" style="padding: 30px; font-size: 12px; color: #888;">
          © 2025 DLT Health Plus. All rights reserved.
        </td>
      </tr>
    </table>
  </center>
</body>
</html>

