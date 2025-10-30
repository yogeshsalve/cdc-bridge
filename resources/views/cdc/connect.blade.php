<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CDC Connector | CVision.AI</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      height: 100vh;
      display: flex;
      overflow: hidden;
      background: #f9fafb;
    }

    .left {
      flex: 1;
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px;
    }

    .left img {
      width: 120px;
      height: 120px;
      margin-bottom: 20px;
      border-radius: 50%;
      box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
    }

    .left h1 {
      font-size: 2.2rem;
      margin-bottom: 10px;
      font-weight: 700;
    }

    .left p {
      font-size: 1.1rem;
      color: #e5e7eb;
      max-width: 300px;
    }

    .right {
      flex: 1;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .panel {
      width: 90%;
      max-width: 420px;
      padding: 40px;
      border-radius: 16px;
      background: #ffffff;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      text-align: center;
      transition: all 0.4s ease;
    }

    .panel h2 {
      margin-bottom: 25px;
      color: #1f2937;
      font-weight: 700;
    }

    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 14px;
      border-radius: 8px;
      border: 1px solid #d1d5db;
      transition: border 0.3s;
    }

    input:focus {
      border-color: #3b82f6;
      outline: none;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button:hover {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      transform: translateY(-2px);
    }

    #status {
      font-size: 14px;
      color: #6b7280;
      margin-top: 10px;
    }

    /* Smooth animation for form visibility */
    .fade-in {
      opacity: 1;
      transform: translateY(0);
      transition: all 0.6s ease;
    }

    .fade-out {
      opacity: 0;
      transform: translateY(20px);
      pointer-events: none;
    }

    .connect-btn {
      padding: 14px 40px;
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .connect-btn:hover {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      transform: scale(1.05);
    }
  </style>
</head>
<body>

  <!-- LEFT SIDE -->
  <div class="left">
    <img src="https://cdn-icons-png.flaticon.com/512/2910/2910768.png" alt="CDC Logo">
    <h1>CDC Data Stream</h1>
    <p>Connect, monitor, and visualize your database changes in real time.</p>
  </div>

  <!-- RIGHT SIDE -->
  <div class="right">
    <div class="panel">
      <button id="startBtn" class="connect-btn" onclick="showForm()">🔗 Connect</button>

      <div id="formContainer" class="fade-out">
        <h2>Database Connection</h2>
        <input type="text" id="db_host" placeholder="Host (e.g. localhost)">
        <input type="text" id="db_user" placeholder="User (e.g. root)">
        <input type="password" id="db_pass" placeholder="Password">
        <input type="text" id="db_name" placeholder="Database Name">
        <input type="text" id="db_table" placeholder="Table (e.g. users)">
        <button onclick="connectDB()">Save & Connect</button>
        <p id="status"></p>
      </div>
    </div>
  </div>

  <script>
    function showForm() {
      const btn = document.getElementById('startBtn');
      const form = document.getElementById('formContainer');

      btn.classList.add('fade-out');
      setTimeout(() => {
        btn.style.display = 'none';
        form.classList.remove('fade-out');
        form.classList.add('fade-in');
      }, 400);
    }

    async function connectDB() {
      const host = document.getElementById('db_host').value.trim();
      const user = document.getElementById('db_user').value.trim();
      const pass = document.getElementById('db_pass').value.trim();
      const db = document.getElementById('db_name').value.trim();
      const table = document.getElementById('db_table').value.trim();
      const status = document.getElementById('status');

      if (!host || !user || !db || !table) {
        status.textContent = "⚠️ Please fill all fields.";
        return;
      }

      status.textContent = "⏳ Connecting...";

      try {
        const response = await axios.post("http://127.0.0.1:8001/set-connection", {
          host, user, password: pass, database: db, table
        });

        if (response.data.message && !response.data.error) {
          status.textContent = `✅ Connected successfully to ${db}.${table}`;
          setTimeout(() => {
            window.location.href = "{{ url('/live-dashboard') }}";
          }, 1200);
        } else {
          status.textContent = `❌ ${response.data.error || "Connection failed"}`;
        }
      } catch (err) {
        console.error(err);
        status.textContent = "❌ Could not connect. Check CDC service.";
      }
    }
  </script>
</body>
</html>
