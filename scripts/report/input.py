from .utils import log, fail, PALETTE
import json, sys

def read_input() -> dict:
    try:
        raw = sys.stdin.read()
        if not raw.strip():
            fail("No input received on STDIN")
        data = json.loads(raw)
        log(f"Received input with keys: {list(data.keys())}")
        return data
    except json.JSONDecodeError as e:
        fail(f"Invalid JSON input: {e}")

def validate_input(data: dict) -> None:
    required_keys = [
        'period', 'total_orders', 'total_revenue',
        'total_returns', 'return_rate_pct', 'output_path'
    ]
    missing = [k for k in required_keys if k not in data]
    if missing:
        fail(f"Missing required fields: {missing}")
