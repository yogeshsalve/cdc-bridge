from flask import Flask, request, jsonify
from flask_cors import CORS
import psycopg2
import mysql.connector
import threading
import time
import json

app = Flask(__name__)
CORS(app)

# Global variables
current_config = None
history_data = []
last_record = None

# --- Helper: Connect to database based on type ---
def connect_db(cfg):
    try:
        if cfg["db_type"] == "postgresql":
            conn = psycopg2.connect(
                host=cfg["host"],
                user=cfg["user"],
                password=cfg["password"],
                dbname=cfg["database"]
            )
        elif cfg["db_type"] == "mysql":
            conn = mysql.connector.connect(
                host=cfg["host"],
                user=cfg["user"],
                password=cfg["password"],
                database=cfg["database"]
            )
        else:
            raise ValueError("Unsupported database type")
        return conn
    except Exception as e:
        print("❌ DB connection failed:", e)
        return None


# --- CDC Monitoring Thread ---
def monitor_cdc():
    global current_config, history_data, last_record

    if not current_config:
        return

    conn = connect_db(current_config)
    if not conn:
        print("⚠️ Unable to establish database connection.")
        return

    cursor = conn.cursor(dictionary=True if current_config["db_type"] == "mysql" else False)
    table = current_config["table"]

    print(f"🔄 Monitoring table: {table}")

    last_id = None

    while True:
        try:
            query = f"SELECT * FROM {table} ORDER BY id DESC LIMIT 1"
            cursor.execute(query)
            row = cursor.fetchone()

            if not row:
                time.sleep(5)
                continue

            # Convert PostgreSQL result to dict if needed
            if current_config["db_type"] == "postgresql" and row:
                colnames = [desc[0] for desc in cursor.description]
                row = dict(zip(colnames, row))

            # Identify new record
            if not last_record or row["id"] != last_id:
                print("🆕 New record detected:", row)
                last_record = {
                    "table_name": table,
                    "operation": "insert",
                    "data": row,
                    "created_at": time.strftime("%Y-%m-%d %H:%M:%S")
                }
                history_data.append(last_record)
                last_id = row["id"]

            time.sleep(5)
        except Exception as e:
            print("❌ Error in CDC loop:", e)
            time.sleep(10)
            continue


@app.route('/set-connection', methods=['POST'])
def set_connection():
    global current_config, history_data, last_record

    data = request.get_json()
    print("Received config:", data)

    required = ["db_type", "host", "user", "password", "database", "table"]
    if not all(k in data for k in required):
        return jsonify({"error": "Missing required connection fields"}), 400

    # Store current connection details
    current_config = data
    history_data = []
    last_record = None

    # Test connection
    conn = connect_db(data)
    if not conn:
        return jsonify({"error": "Failed to connect to database"}), 500

    conn.close()

    # Start monitoring thread
    thread = threading.Thread(target=monitor_cdc, daemon=True)
    thread.start()

    return jsonify({"message": f"Connected successfully to {data['db_type']} database"})


@app.route('/api/connectors', methods=['GET'])
def get_connectors():
    return jsonify({
        "live": last_record,
        "history": history_data
    })


@app.route('/disconnect', methods=['POST'])
def disconnect():
    global current_config, last_record, history_data
    current_config = None
    last_record = None
    history_data = []
    return jsonify({"message": "Disconnected successfully"})


if __name__ == '__main__':
    print("🚀 CDC Bridge Service running on http://127.0.0.1:8001")
    app.run(host="127.0.0.1", port=8001, debug=True)
