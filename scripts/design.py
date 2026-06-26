# design.py
"""Design helpers and constants for premium PDF reports.

This module centralises colors, fonts, sizes, and reusable styling helpers used
by :pyclass:`ReportBuilder` in ``generate_report.py``. Keeping design concerns
in a single place makes future branding updates straightforward.
"""

from reportlab.lib.units import inch

# ---------------------------------------------------------------------------
# Colour palette – modern, premium look
# ---------------------------------------------------------------------------
PALETTE = {
    "primary": "#0F172A",   # navy‑like deep shade
    "secondary": "#64748B",  # soft gray
    "accent": "#2563EB",     # bright blue accent
    "background": "#FFFFFF", # pure white for default
    "card_bg": "#F8FAFC",    # light gray for cards / table rows
    "border": "#E2E8F0",     # subtle border colour
    "success": "#10B981",
    "warning": "#F59E0B",
}

# ---------------------------------------------------------------------------
# Font handling
# ---------------------------------------------------------------------------
# Register Inter font – fallback to Helvetica if not available.
# The font file should be placed in an ``assets`` folder next to this module.




# ---------------------------------------------------------------------------
# Card dimensions – used for metric cards
# ---------------------------------------------------------------------------
# Width is calculated to fit four cards across the page with margins.
PAGE_MARGIN = 0.75 * inch
PAGE_WIDTH = 8.5 * inch  # Letter width

# ---------------------------------------------------------------------------
# Icon paths for metric cards – placeholders can be swapped with real SVG/PNG.
# ---------------------------------------------------------------------------


# ---------------------------------------------------------------------------
# Theme application – background handling (white or dark mode)
# ---------------------------------------------------------------------------

