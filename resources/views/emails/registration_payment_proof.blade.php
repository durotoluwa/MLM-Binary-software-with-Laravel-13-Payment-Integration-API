 



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
          <h2 style="margin: 0; font-size: 22px; color: #333;">New Registration Payment Submitted</h2>
        </td>
      </tr>
 
      <!-- User Details -->
      <tr>
        <td style="padding: 20px 40px; font-size: 16px; color: #555;">
       <p>A new user has submitted proof of payment for registration:</p>
 <p>
<ul>
    <li><strong>Name:</strong> {{ $data['user']->first_name }} {{ $data['user']->last_name }}</li>
    <li><strong>Email:</strong> {{ $data['user']->email }}</li>
    <li><strong>Phone:</strong> {{ $data['user']->phone }}</li>
    <li><strong>Bank:</strong> {{ $data['transaction']->bank_name }}</li>
    <li><strong>Sender:</strong> {{ $data['transaction']->sendername }}</li>
    <li><strong>Transaction No:</strong> {{ $data['transaction']->transaction_no ?? 'N/A' }}</li>
    <li><strong>Submitted At:</strong> {{ $data['transaction']->created_at }}</li>
</ul>

@if($data['transaction']->proof)
    <p><strong>Proof of Payment:</strong></p>
    <p>
        <a href="{{ asset($data['transaction']->proof) }}" target="_blank">
            View Proof
        </a>
    </p>
@endif

 </p>
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

