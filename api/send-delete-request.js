export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method Not Allowed' });
  }

  const { feedback, name, email, username, phone, reason, confirmDelete, website_hp } = req.body || {};

  if (website_hp) {
    return res.redirect(302, '/');
  }

  if (!feedback || !name || !email || !username || !confirmDelete) {
    return res.redirect(302, '/');
  }
  const brevoPayload = {
    sender: { name: "MyFarmSight", email: "info@myfarmsight.com" },
    to: [{ email: "info@myfarmsight.com", name: "Admin" }],
    replyTo: { email: email, name: name },
    subject: `Account Deletion Request - ${username}`,
    textContent: `Account Deletion Request Submitted

Name:      ${name}
Email:     ${email}
Username:  ${username}
Phone:     ${phone || "N/A"}
Reason:    ${reason || "N/A"}

Feedback:
${feedback}
------------------------------------
Confirmed irreversible deletion: YES
Submitted On: ${new Date().toISOString()}`
  };

  try {
    const response = await fetch('https://api.brevo.com/v3/smtp/email', {
      method: 'POST',
      headers: {
        'accept': 'application/json',
        'content-type': 'application/json',
        'api-key': process.env.BREVO_API_KEY
      },
      body: JSON.stringify(brevoPayload)
    });

    if (!response.ok) {
      throw new Error(`Brevo API Error: ${response.statusText}`);
    }

    // 7. Redirect on success
    return res.redirect(302, '/?status=success');
  } catch (error) {
    console.error('Email sending failed:', error);
    return res.status(500).json({ error: 'Failed to send account deletion request' });
  }
}