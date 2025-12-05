# biometric_listener.py (example)
import requests, time, json

SERVER_URL = "http://YOUR_PC_IP/veeramalla_attendance_portal/attendance_ingest_bio.php"

def capture_from_device():
    # Use your device SDK here to capture fingerprint and get biometric_id or regd_no
    # Example: biometric_id = sdk.capture_template()  (base64 string)
    # If SDK returns a matched regd_no directly, use that.
    biometric_id = None
    regd_no = None
    # Implementation depends on SDK; below is placeholder
    # Wait for a finger, capture, optionally match internally and get regd_no
    return biometric_id, regd_no

if __name__ == "__main__":
    print("Biometric listener started...")
    while True:
        b_id, regd = capture_from_device()
        if b_id or regd:
            payload = {"biometric_id": b_id, "regd_no": regd, "timestamp": time.strftime("%Y-%m-%d %H:%M:%S")}
            r = requests.post(SERVER_URL, json=payload, timeout=10)
            print("Server resp:", r.text)
        time.sleep(0.5)
