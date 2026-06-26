from .utils import log, fail, PALETTE
import json, sys

def build_prompt(data: dict) -> str:
    period = data['period']
    # Category breakdown
    category_lines = []
    for cat in data.get('revenue_by_category', []):
        category_lines.append(
            f"  - {cat['category']}: {cat['orders']} orders, "
            f"${cat['revenue']:,.2f} revenue ({cat['pct_of_total']:.1f}% of total)"
        )
    category_text = "\n".join(category_lines) if category_lines else "  No category data available"
    # Top products
    product_lines = []
    for p in data.get('top_products', []):
        product_lines.append(
            f"  - {p['name']} (SKU: {p['sku']}): ${p['revenue']:,.2f} revenue, {p['orders']} orders"
        )
    product_text = "\n".join(product_lines) if product_lines else "  No product data available"
    prompt = f"""You are a senior business analyst writing an executive summary for a business report.
Write a concise, professional executive summary of 2-3 paragraphs based on the following data.
Be specific with numbers and percentages. Highlight key trends, notable performances, and provide actionable insights. Use a confident, analytical tone.

REPORT DATA:
=============
Period: {period['from']} to {period['to']}

Key Metrics:
  - Total Orders: {data['total_orders']}
  - Total Revenue: ${data['total_revenue']:,.2f}
  - Total Returns: {data['total_returns']}
  - Return Rate: {data['return_rate_pct']:.1f}%
  - Total Refunds: ${data.get('total_refunds', 0):,.2f}

Revenue by Category:
{category_text}

Top Products by Revenue:
{product_text}

Write the executive summary now. Do not include any headings or bullet points — write flowing paragraphs only.
"""
    return prompt
