<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CDC Connectors</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    @media (prefers-color-scheme: dark) {
      :root {
        --bg: #0f172a;
        --text: #e5e7eb;
        --panel-bg: #1e293b;
        --card-bg: #334155;
        --pre-bg: #0f172a;
        --pre-text: #f9fafb;
        --border-top-live: #60a5fa;
        --border-top-history: #34d399;
        --shadow: rgba(0, 0, 0, 0.4);
        --timestamp: #9ca3af;
      }
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      padding: 20px;
      display: flex;
      flex-direction: row;
      gap: 25px;
      flex-wrap: wrap;
      transition: background 0.3s, color 0.3s;
    }

    h2 {
      margin-bottom: 15px;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .left-panel, .right-panel {
      background: var(--panel-bg);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px var(--shadow);
      transition: background 0.3s, box-shadow 0.3s;
    }

    .left-panel {
      flex: 2;
      border-top: 4px solid var(--border-top-live);
      min-width: 300px;
    }

    .right-panel {
      flex: 1;
      border-top: 4px solid var(--border-top-history);
      min-width: 300px;
    }

    .card {
      background: var(--card-bg);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 12px;
      box-shadow: 0 1px 3px var(--shadow);
      transition: transform 0.2s ease, background 0.3s;
    }

    .card:hover {
      transform: scale(1.01);
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

    button {
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s ease;
      box-shadow: 0 2px 6px rgba(59,130,246,0.3);
    }

    button:hover {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      transform: translateY(-1px);
    }

    #toggle-history-btn {
      background: linear-gradient(90deg, #ef4444, #dc2626);
      box-shadow: 0 2px 6px rgba(239,68,68,0.3);
    }

    #toggle-history-btn:hover {
      background: linear-gradient(90deg, #dc2626, #b91c1c);
    }

    #history-container {
      margin-top: 20px;
    }

    .hidden {
      display: none;
    }

    .timestamp {
      font-size: 12px;
      color: var(--timestamp);
      margin-top: 5px;
      display: block;
    }

    .top-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 10px;
    }

    .fade-in {
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(5px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ✅ RESPONSIVE DESIGN */
    @media (max-width: 1024px) {
      body {
        flex-direction: column;
        align-items: stretch;
      }

      .left-panel, .right-panel {
        width: 100%;
      }

      .left-panel {
        border-top-width: 5px;
      }
    }

    @media (max-width: 600px) {
      body {
        padding: 10px;
      }

      h2 {
        font-size: 18px;
      }

      button {
        width: 100%;
      }

      pre {
        font-size: 12px;
        max-height: 140px;
      }
    }
  </style>
</head>

<body>
  <div class="left-panel">
    <h2>📡 Live CDC Updates</h2>
    <div id="live-container"></div>
  </div>

  <div class="right-panel" id="history-panel">
    <h2>📜 History</h2>
    <div class="top-buttons">
      <button id="load-history-btn">Load Historical Data</button>
      <button id="toggle-history-btn">Hide History</button>
    </div>
    <div id="history-container"></div>
  </div>

  <script>
    let liveData = [];
    let pollingInterval = null;
    let historyVisible = true;

    // ✅ Fetch last 5-minute data for live section
    async function fetchLiveCDCData() {
      try {
        const response = await axios.get('/api/connectors');
        const data = response.data;
        const container = document.getElementById('live-container');

        if (Array.isArray(data)) {
          const now = new Date();
          liveData = data.filter(item => {
            const t = new Date(item.created_at);
            return (now - t) / 1000 <= 300; // last 5 min
          });
        }

        container.innerHTML = '';
        if (liveData.length === 0) {
          container.innerHTML = '<p style="color:var(--timestamp);">No recent CDC data in last 5 minutes...</p>';
          return;
        }

        liveData.slice(0, 10).forEach(item => {
          const card = document.createElement('div');
          card.className = 'card fade-in';
          card.innerHTML = `
            <strong>Table:</strong> ${item.table_name ?? 'N/A'} <br>
            <strong>Operation:</strong> ${item.operation ?? 'N/A'} <br>
            <strong>Data:</strong>
            <pre>${JSON.stringify(item.data, null, 2)}</pre>
            <span class="timestamp">⏱ ${item.created_at ?? '—'}</span>
          `;
          container.appendChild(card);
        });
      } catch (error) {
        console.error('Error fetching live CDC data:', error);
      }
    }

    // Load historical data
    async function loadHistoricalData() {
      const btn = document.getElementById('load-history-btn');
      const container = document.getElementById('history-container');
      btn.disabled = true;
      btn.innerText = 'Loading...';

      try {
        const response = await axios.get('/api/connectors');
        const data = response.data;
        container.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {
          container.innerHTML = '<p style="color:var(--timestamp);">No historical data found.</p>';
        } else {
          data.slice().reverse().forEach(item => {
            const card = document.createElement('div');
            card.className = 'card fade-in';
            card.innerHTML = `
              <strong>Table:</strong> ${item.table_name ?? 'N/A'} <br>
              <strong>Operation:</strong> ${item.operation ?? 'N/A'} <br>
              <strong>Data:</strong>
              <pre>${JSON.stringify(item.data, null, 2)}</pre>
              <span class="timestamp">⏱ ${item.created_at ?? '—'}</span>
            `;
            container.appendChild(card);
          });
        }
      } catch (error) {
        console.error('Error loading historical data:', error);
        container.innerHTML = '<p style="color:red;">Failed to load historical data.</p>';
      }

      btn.disabled = false;
      btn.innerText = 'Load Historical Data';
    }

    // Hide/Show history
    document.getElementById('toggle-history-btn').addEventListener('click', () => {
      const panel = document.getElementById('history-panel');
      historyVisible = !historyVisible;
      document.getElementById('history-container').classList.toggle('hidden');
      const btn = document.getElementById('toggle-history-btn');
      btn.innerText = historyVisible ? 'Hide History' : 'Show History';
    });

    // Start auto-refresh
    fetchLiveCDCData();
    pollingInterval = setInterval(fetchLiveCDCData, 3000);

    document.getElementById('load-history-btn').addEventListener('click', loadHistoricalData);
  </script>
</body>
</html>
