<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live CDC Dashboard | CVision.AI</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #f9fafb;
      color: #1f2937;
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    header {
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      color: white;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    header h1 {
      font-size: 1.6rem;
      font-weight: 700;
    }

    header button {
      background: white;
      color: #2563eb;
      border: none;
      padding: 10px 20px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    header button:hover {
      background: #e0e7ff;
      transform: scale(1.05);
    }

    main {
      display: flex;
      flex: 1;
      padding: 30px;
      gap: 30px;
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .card h2 {
      font-size: 1.3rem;
      margin-bottom: 15px;
      color: #1f2937;
      font-weight: 600;
    }

    #live-container pre {
      background: #f3f4f6;
      border-radius: 8px;
      padding: 15px;
      overflow-x: auto;
      font-size: 14px;
    }

    #status {
      font-size: 0.95rem;
      margin-top: 10px;
      color: #6b7280;
    }

    .history-table {
      width: 100%;
      border-collapse: collapse;
    }

    .history-table th, .history-table td {
      padding: 10px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 0.9rem;
    }

    .history-table th {
      background: #f3f4f6;
      text-align: left;
      color: #374151;
      font-weight: 600;
    }

    .fade-in {
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <header>
    <h1>📡 Live CDC Dashboard</h1>
    <button onclick="disconnect()">Disconnect</button>
  </header>

  <main>
    <div class="card" style="flex: 1.2">
      <h2>🔴 Live Stream</h2>
      <div id="live-container">
        <p style="color:#6b7280;">Waiting for live CDC updates...</p>
      </div>
      <p id="status"></p>
    </div>

    <div class="card" style="flex: 1.5">
      <h2>📜 Recent History</h2>
      <div style="overflow-y: auto; max-height: 70vh;">
        <table class="history-table" id="history-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Table</th>
              <th>Operation</th>
              <th>Data</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody id="history-body">
            <tr><td colspan="5" style="text-align:center; color:#9ca3af;">No data yet</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script>
    let lastLiveRecord = null;

    async function fetchLiveCDCData() {
      try {
        const response = await axios.get("/api/connectors");
        const data = response.data;
        const container = document.getElementById("live-container");
        const status = document.getElementById("status");

        // Handle no data
        if (!data || !data.live) {
          status.textContent = "⚪ No live data yet.";
          return;
        }

        const latest = data.live.data || data.live;

        // Update live card
        container.innerHTML = `
          <div class="fade-in">
            <strong>Table:</strong> ${latest.table_name ?? "N/A"}<br>
            <strong>Operation:</strong> ${latest.operation ?? "N/A"}<br><br>
            <strong>Data:</strong>
            <pre>${JSON.stringify(latest.data ?? latest, null, 2)}</pre>
          </div>
        `;

        // Update status
        status.textContent = "🟢 Live data stream active";

        // Update history table
        const historyBody = document.getElementById("history-body");
        historyBody.innerHTML = data.history.map(row => `
          <tr>
            <td>${row.id}</td>
            <td>${row.table_name}</td>
            <td>${row.operation}</td>
            <td><pre style="white-space:pre-wrap; font-size:12px;">${JSON.stringify(row.data, null, 2)}</pre></td>
            <td>${row.created_at}</td>
          </tr>
        `).join('');

      } catch (error) {
        console.error("Error fetching CDC data:", error);
        document.getElementById("status").textContent = "🔴 Disconnected from service";
      }
    }

    function disconnect() {
      // Redirect to connect page (simulate disconnect)
      window.location.href = "{{ url('/') }}";
    }

    // Poll live data every 3 seconds
    setInterval(fetchLiveCDCData, 3000);
    fetchLiveCDCData();
  </script>

</body>
</html>
