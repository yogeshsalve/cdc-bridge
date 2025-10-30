<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CDC Connectors Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        :root {
            --bg: #f5f7fa;
            --text: #1f2937;
            --panel-bg: #fff;
            --card-bg: #f9fafb;
            --pre-bg: #111827;
            --pre-text: #e5e7eb;
            --border-top-live: #3b82f6;
            --border-top-history: #10b981;
            --shadow: rgba(0, 0, 0, 0.08);
            --timestamp: #6b7280;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .container { display: flex; flex-wrap: wrap; gap: 20px; }
        .panel {
            background: var(--panel-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px var(--shadow);
            flex: 1;
            min-width: 350px;
        }
        h2 { font-size: 20px; margin-bottom: 15px; }
        input, button {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            margin-bottom: 8px;
        }
        button {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
        }
        .card {
            background: var(--card-bg);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px var(--shadow);
        }
        pre {
            background: var(--pre-bg);
            color: var(--pre-text);
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            overflow: auto;
            max-height: 180px;
        }
        .timestamp {
            font-size: 12px;
            color: var(--timestamp);
            margin-top: 4px;
            display: block;
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        .blink { animation: blink 1s infinite; }
    </style>
</head>
<body>

    <div class="panel">
        <h2>⚙️ Configure Database Connection</h2>
        <div id="config-form">
            <input type="text" id="db_host" placeholder="Database Host (e.g. localhost)">
            <input type="text" id="db_user" placeholder="Database User (e.g. root)">
            <input type="password" id="db_pass" placeholder="Database Password">
            <input type="text" id="db_name" placeholder="Database Name">
            <input type="text" id="db_table" placeholder="Table Name (e.g. users)">
            <button onclick="setConnection()">Save & Connect</button>
        </div>
        <p id="config-status" style="margin-top:10px; color:#6b7280;"></p>
    </div>

    <div class="container">
        <div class="panel" style="border-top: 4px solid var(--border-top-live);">
            <h2>📡 Live CDC Updates <span id="status" style="font-size:14px;color:#6b7280;"></span></h2>
            <div id="live-container"></div>
        </div>

        <div class="panel" style="border-top: 4px solid var(--border-top-history);">
            <h2>📜 History</h2>
            <button onclick="loadHistory()">Load History</button>
            <div id="history-container" style="margin-top:10px;"></div>
        </div>
    </div>

    <script>
    let lastLiveRecord = null;
    let pollingInterval = null;
    let connected = false;

    async function setConnection() {
        const host = document.getElementById("db_host").value.trim();
        const user = document.getElementById("db_user").value.trim();
        const password = document.getElementById("db_pass").value.trim();
        const database = document.getElementById("db_name").value.trim();
        const table = document.getElementById("db_table").value.trim();
        const status = document.getElementById("config-status");

        status.textContent = "⏳ Connecting...";

        if (!host || !user || !database || !table) {
            status.textContent = "⚠️ Please fill all fields.";
            return;
        }

        try {
            const response = await axios.post("http://127.0.0.1:8001/set-connection", {
                host, user, password, database, table
            });

            if (response.data.message && !response.data.error) {
                status.textContent = `✅ Connected successfully to ${database}.${table}`;
                connected = true;

                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(fetchLiveCDCData, 10000);
                fetchLiveCDCData();
            } else {
                status.textContent = `❌ ${response.data.error || "Connection failed"}`;
            }
        } catch (error) {
            console.error(error);
            status.textContent = "❌ Failed to connect. Check Python CDC service.";
        }
    }

    // ✅ Fetch live CDC or show last inserted record
    async function fetchLiveCDCData() {
        try {
            const response = await axios.get("/api/connectors");
            const data = response.data;

            const container = document.getElementById("live-container");
            const status = document.getElementById("status");

            if (!data || !data.live) {
                status.textContent = "🔘 No data yet.";
                return;
            }

            const latest = data.live;

            // Update status indicator
            if (!lastLiveRecord || JSON.stringify(lastLiveRecord) !== JSON.stringify(latest)) {
                status.innerHTML = "🟢 New CDC data received!";
                lastLiveRecord = latest;
            } else {
                status.innerHTML = "⚪ Showing last record (no new data yet)";
            }

            // Render latest record
            container.innerHTML = `
                <div class="card fade-in">
                    <strong>Table:</strong> ${latest.table_name ?? "N/A"}<br>
                    <strong>Operation:</strong> ${latest.operation ?? "N/A"}<br>
                    <strong>Data:</strong>
                    <pre>${JSON.stringify(latest.data, null, 2)}</pre>
                </div>
            `;

        } catch (error) {
            console.error("Error fetching CDC data:", error);
            document.getElementById("status").innerHTML = "🔴 Disconnected from service";
        }
    }

    // 🕒 Load full history
    async function loadHistory() {
        try {
            const res = await axios.get("/api/connectors");
            const data = res.data;
            const container = document.getElementById("history-container");
            container.innerHTML = "";

            if (!data || !Array.isArray(data.history) || data.history.length === 0) {
                container.innerHTML = "<p style='color:gray;'>No history found.</p>";
                return;
            }

            data.history.forEach(item => {
                const card = document.createElement("div");
                card.className = "card fade-in";
                card.innerHTML = `
                    <strong>Table:</strong> ${item.table_name ?? 'N/A'}<br>
                    <strong>Operation:</strong> ${item.operation ?? 'N/A'}<br>
                    <strong>Data:</strong>
                    <pre>${JSON.stringify(item.data, null, 2)}</pre>
                    <span class="timestamp">⏱ ${item.created_at ?? '—'}</span>
                `;
                container.appendChild(card);
            });
        } catch (err) {
            console.error("Error loading history:", err);
        }
    }

    // Start polling every 10 seconds
    fetchLiveCDCData();
    pollingInterval = setInterval(fetchLiveCDCData, 10000);
    </script>
</body>
</html>
