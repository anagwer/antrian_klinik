<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Lestari iPhone Prediksi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --bg-canvas:   #0d1117;
  --bg-surface:  #161b22;
  --bg-elevated: #1c2128;
  --border:      #30363d;
  --text-primary:#e6edf3;
  --text-secondary:#8b949e;
  --accent-blue: #2f81f7;
  --green:       #3fb950;
  --green-subtle:#1f6a2022;
  --red:         #f85149;
  --red-subtle:  #a9220022;
  --radius:      6px;
  --radius-lg:   10px;
  --font-sans:   'Sora', sans-serif;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--bg-canvas);
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 14px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.login-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  width: 400px;
  max-width: 100%;
  padding: 40px 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.login-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 50%;
  margin-bottom: 16px;
  color: var(--text-primary);
}

.login-title {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 6px;
}

.login-subtitle {
  font-size: 13px;
  color: var(--text-secondary);
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 6px;
  font-weight: 500;
}

.form-input {
  background: var(--bg-canvas);
  border: 1px solid var(--border);
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 14px;
  padding: 10px 14px;
  border-radius: var(--radius);
  width: 100%;
  outline: none;
  transition: border-color .15s, background .15s;
}

.form-input:focus {
  border-color: var(--accent-blue);
  background: var(--bg-elevated);
}

.login-btn {
  background: var(--accent-blue);
  color: #fff;
  border: none;
  font-family: var(--font-sans);
  font-size: 14px;
  font-weight: 600;
  padding: 12px;
  border-radius: var(--radius);
  width: 100%;
  cursor: pointer;
  transition: opacity .15s, transform .1s;
  margin-top: 10px;
}

.login-btn:hover {
  opacity: 0.9;
}

.login-btn:active {
  transform: scale(0.98);
}

.alert {
  padding: 12px 14px;
  border-radius: var(--radius);
  font-size: 12px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-success {
  background: var(--green-subtle);
  color: var(--green);
  border: 1px solid var(--green)44;
}

.alert-danger {
  background: var(--red-subtle);
  color: var(--red);
  border: 1px solid var(--red)44;
}
</style>
</head>
<body>

<div class="login-card">
  <div class="login-header">
    <div class="login-logo">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
        <line x1="12" y1="18" x2="12.01" y2="18"/>
      </svg>
    </div>
    <h1 class="login-title">Lestari iPhone</h1>
    <p class="login-subtitle">Forecasting & Inventory Management</p>
  </div>

  <?php if ($this->session->flashdata('success')) : ?>
    <div class="alert alert-success">
      <span><?= $this->session->flashdata('success') ?></span>
      <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;">✕</button>
    </div>
  <?php elseif ($this->session->flashdata('error')) : ?>
    <div class="alert alert-danger">
      <span><?= $this->session->flashdata('error') ?></span>
      <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;">✕</button>
    </div>
  <?php endif ?>

  <form method="POST" action="<?= base_url('login/proses_login') ?>">
    <div class="form-group">
      <label class="form-label" for="username">Username</label>
      <input type="text" class="form-input" id="username" name="username" placeholder="Masukkan username" autocomplete="off" required maxlength="15">
    </div>
    
    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <input type="password" class="form-input" id="password" name="password" placeholder="Masukkan password" required>
    </div>

    <button type="submit" class="login-btn">Masuk ke Sistem</button>
  </form>
</div>

</body>
</html>
