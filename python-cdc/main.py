from fastapi import FastAPI
import mysql.connector
from config import DB_CONFIG
import time

app = FastAPI()

# Keep track of last seen user ID
last_seen_id = 0

@app.get("/")
def home():
    return {"message": "Python CDC Service Running"}

@app.get("/poll")
def poll_changes():
    global last_seen_id

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)

    # Fetch records newer than last_seen_id
    cursor.execute(f"SELECT * FROM users WHERE id > {last_seen_id} ORDER BY id ASC")
    changes = cursor.fetchall()

    # Update last_seen_id if new data exists
    if changes:
        last_seen_id = changes[-1]['id']

    conn.close()
    return {"new_records": changes, "last_seen_id": last_seen_id}
