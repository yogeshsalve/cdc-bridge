<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Live CDC Monitor</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f5f7fa;
    padding: 20px;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
button {
    background: linear-gradient(90deg, #ef4444, #dc2626);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
}
button:hover { background: #b91c1c; }
.panel {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}
.card {
    background: #f9fafb;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}
pre {
    background: #111827;
    color: #e5e7eb;
    padding: 8px;
    border-radius: 8px;
    overflow: auto;
    max-height: 180px;
}
</style>
</head>
<body>
<div class="header">
    <h2>📡 Live CDC Monitor</h2>
    <form method="POST" action="/disconnect">
        @csrf
        <button type="submit">🔴 Disconnect</button>
    </form>
</div>

<div class="panel" style="border-top:4px solid #3b82f6;">
    <h3>Live CDC Updates <span id="status" style="font-size:14px;color:#6b7280;"></span></h3>
    <div id="live-container"></div>
</div>

<div class="panel" style="border-top:4px solid #10b981;">
    <h3>📜 CDC History</h3>
    <button onclick="loadHistory()">🔁 Refresh History</button>
    <div id="history-container" style="margin-top:10px;"></div>
</div>

<script>
let lastLiveRecord = null;
let polling = null;

async function fetchLive() {
    try {
        const res = await axios.get("/api/connectors");
        const data = res.data;
        const status = document.getElementById('status');
        const container = document.getElementById('live-container');

        if (!data.live) {
            status.textContent = "⚪ No live data yet.";
            return;
        }

        const record = data.live;
        status.innerHTML = "🟢 Latest Record";

        container.innerHTML = `
            <div class="card">
                <strong>Table:</strong> ${record.table_name ?? 'N/A'}<br>
                <strong>Operation:</strong> ${record.operation ?? 'N/A'}<br>
                <strong>Data:</strong>
                <pre>${JSON.stringify(record.data, null, 2)}</pre>
            </div>
        `;
    } catch (err) {
        document.getElementById('status').innerHTML = "🔴 Disconnected from service";
    }
}

async function loadHistory() {
    try {
        const res = await axios.get("/api/connectors");
        const hist = res.data.history || [];
        const container = document.getElementById('history-container');
        container.innerHTML = "";

        hist.forEach(item => {
            const div = document.createElement("div");
            div.className = "card";
            div.innerHTML = `
                <strong>Table:</strong> ${item.table_name}<br>
                <strong>Operation:</strong> ${item.operation}<br>
                <strong>Data:</strong>
                <pre>${JSON.stringify(item.data, null, 2)}</pre>
                <small>🕒 ${item.created_at}</small>
            `;
            container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
    }
}

fetchLive();
loadHistory();
polling = setInterval(fetchLive, 10000);
</script>
</body>
</html>
