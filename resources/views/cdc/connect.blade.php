<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CDC Connector Setup</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #1f2937;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .panel {
            background: white;
            border-radius: 12px;
            padding: 30px;
            width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }
        button {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
        }
        #status {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="panel">
    <h2>⚙️ Connect to Database</h2>
    <input type="text" id="db_host" placeholder="Host (e.g. localhost)">
    <input type="text" id="db_user" placeholder="User (e.g. root)">
    <input type="password" id="db_pass" placeholder="Password">
    <input type="text" id="db_name" placeholder="Database Name">
    <input type="text" id="db_table" placeholder="Table (e.g. users)">
    <button onclick="connectDB()">Save & Connect</button>
    <p id="status"></p>
</div>

<script>
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
            // Redirect to live dashboard after success
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
