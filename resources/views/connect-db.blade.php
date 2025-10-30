<form id="connectionForm" style="max-width:600px;margin:auto;">
    <h2>🔌 Connect to Your Database</h2>
    <label>Database Type</label>
    <select name="db_type" required>
        <option value="mysql">MySQL</option>
        <option value="postgresql">PostgreSQL</option>
    </select>

    <label>Host</label>
    <input type="text" name="host" value="127.0.0.1" required>

    <label>Port</label>
    <input type="text" name="port" value="3306" required>

    <label>Username</label>
    <input type="text" name="username" value="root" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Database Name</label>
    <input type="text" name="database_name" required>

    <label>Table Name</label>
    <input type="text" name="table_name" required>

    <button type="submit">Connect</button>
</form>

<script>
document.getElementById('connectionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    const res = await fetch('/api/save-connection', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify(data)
    });

    const result = await res.json();
    alert(result.message || 'Connection saved!');
});
</script>
