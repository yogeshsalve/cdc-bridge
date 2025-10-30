from fastapi import FastAPI, Request
import mysql.connector
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allow Laravel frontend
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Global config (updated when /set-connection is called)
DB_CONFIG = {}
TABLE_NAME = None
LAST_SEEN_ID = 0


@app.get("/")
def home():
    return {"message": "✅ Python CDC Service Running"}


@app.post("/set-connection")
async def set_connection(request: Request):
    """
    Expected JSON:
    {
        "host": "127.0.0.1",
        "user": "root",
        "password": "yourpassword",
        "database": "your_db",
        "table": "users"
    }
    """
    global DB_CONFIG, TABLE_NAME, LAST_SEEN_ID
    body = await request.json()

    # Basic validation
    required_fields = ["host", "user", "database", "table"]
    missing = [field for field in required_fields if not body.get(field)]
    if missing:
        return {"error": f"Missing fields: {', '.join(missing)}"}

    DB_CONFIG = {
        "host": body["host"],
        "user": body["user"],
        "password": body.get("password", ""),
        "database": body["database"],
    }
    TABLE_NAME = body["table"]
    LAST_SEEN_ID = 0  # Reset each time connection changes

    try:
        # ✅ Test DB connection
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SELECT NOW()")
        conn.close()

        return {
            "message": "✅ Connection successful",
            "db": DB_CONFIG,
            "table": TABLE_NAME
        }

    except Exception as e:
        return {"error": f"❌ Database connection failed: {str(e)}"}


@app.get("/poll")
def poll_changes():
    global LAST_SEEN_ID, DB_CONFIG, TABLE_NAME

    if not DB_CONFIG or not TABLE_NAME:
        return {"error": "❌ No connection configured. Please call /set-connection first."}

    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        # ✅ Fetch only newer records
        cursor.execute(f"SELECT * FROM `{TABLE_NAME}` WHERE id > %s ORDER BY id ASC", (LAST_SEEN_ID,))
        changes = cursor.fetchall()

        # ✅ If no new data, return the last seen record (for UI continuity)
        if not changes:
            cursor.execute(f"SELECT * FROM `{TABLE_NAME}` ORDER BY id DESC LIMIT 1")
            last_record = cursor.fetchone()
            conn.close()
            return {"new_records": [last_record] if last_record else [], "last_seen_id": LAST_SEEN_ID}

        # ✅ Update the last seen ID
        LAST_SEEN_ID = changes[-1]["id"]
        conn.close()

        return {"new_records": changes, "last_seen_id": LAST_SEEN_ID}

    except Exception as e:
        return {"error": f"Polling failed: {str(e)}"}
