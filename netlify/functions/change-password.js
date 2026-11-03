// netlify/functions/change-password.js
// Handles POST /change-password — validates and simulates password update

// In-memory store (resets on cold start — stateless by design for demo)
let currentPassword = "instagram123";

exports.handler = async (event) => {
  if (event.httpMethod !== "POST") {
    return { statusCode: 405, body: JSON.stringify({ error: "Method not allowed" }) };
  }

  let body;
  try {
    body = JSON.parse(event.body || "{}");
  } catch {
    return { statusCode: 400, body: JSON.stringify({ error: "Invalid JSON" }) };
  }

  const { old_password, new_password, confirm_new_password } = body;

  if (!old_password || !new_password || !confirm_new_password) {
    return {
      statusCode: 400,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ error: "All password fields are required." }),
    };
  }

  if (new_password !== confirm_new_password) {
    return {
      statusCode: 400,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ error: "New passwords do not match." }),
    };
  }

  if (new_password.length < 6) {
    return {
      statusCode: 400,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ error: "New password must be at least 6 characters." }),
    };
  }

  if (old_password !== currentPassword) {
    return {
      statusCode: 401,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ error: "Old password is incorrect." }),
    };
  }

  // Simulate update
  const previous = currentPassword;
  currentPassword = new_password;

  const timestamp = new Date().toISOString();
  console.log(`[PASSWORD CHANGE] [${timestamp}] old="${previous}" new="${new_password}"`);

  return {
    statusCode: 200,
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ message: "Your password has been updated successfully." }),
  };
};
