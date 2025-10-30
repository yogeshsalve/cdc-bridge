<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDC Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow p-4" style="width: 480px;">
    <h3 class="text-center mb-4">🔗 Connect to Your Database</h3>

    <form id="connectForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Database Type</label>
            <select name="db_type" id="db_type" class="form-select" required>
                <option value="postgresql">PostgreSQL</option>
                <option value="mysql">MySQL</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Host</label>
            <input type="text" class="form-control" id="host" name="host" placeholder="e.g. 127.0.0.1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Port</label>
            <input type="number" class="form-control" id="port" name="port" placeholder="5432 (Postgres) or 3306 (MySQL)" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Database Name</label>
            <input type="text" class="form-control" id="database" name="database" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Connect</button>
    </form>

    <div id="responseMessage" class="mt-3 text-center"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.getElementById('connectForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    document.getElementById('responseMessage').innerHTML = "⏳ Connecting...";

    try {
        const res = await axios.post('/api/connect', data);
        if (res.data.success) {
            document.getElementById('responseMessage').innerHTML = "✅ Connected successfully!";
            setTimeout(() => window.location.href = "/live", 1000);
        } else {
            document.getElementById('responseMessage').innerHTML = "❌ " + (res.data.message || 'Connection failed');
        }
    } catch (error) {
        document.getElementById('responseMessage').innerHTML = "⚠️ Connection error: " + error.message;
    }
});
</script>

</body>
</html>
