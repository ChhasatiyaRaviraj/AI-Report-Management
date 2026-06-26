import sys

# Global palette (mirrors original script's PALETTE constant)
PALETTE = {
    "primary": "#1a1a2e",
    "secondary": "#6c757d",
    "accent": "#16213e",
    "background": "#ffffff",
    "background_dark": "#121212",
    "text": "#2d3436",
    "text_light": "#f5f5f5",
    "card_1": "#f0f9ff",
    "card_2": "#f0fdf4",
    "card_3": "#fef9ee",
    "card_4": "#fef2f2",
}

def log(msg: str) -> None:
    """Write a diagnostic message to STDERR."""
    print(f"[generate_report] {msg}", file=sys.stderr)

def fail(msg: str, code: int = 1) -> None:
    """Write an error to STDERR and exit with a non-zero code."""
    print(f"[generate_report] ERROR: {msg}", file=sys.stderr)
    sys.exit(code)
