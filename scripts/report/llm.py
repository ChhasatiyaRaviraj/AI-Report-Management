import os, sys
from .utils import log, fail

def generate_fallback_summary(prompt: str = "") -> str:
    return (
        "This executive summary was generated using a template fallback because the LLM API was not available. "
        "Please configure a valid GROK_API_KEY in your environment to enable AI‑generated summaries.\n\n"
        "The report data has been compiled and is presented in the tables below. "
        "Please review the key metrics, revenue breakdown by category, and top performing products to draw your own conclusions about business performance during the reporting period."
    )

def call_llm(prompt: str) -> str:
    api_key = os.environ.get('GROK_API_KEY', '').strip()
    if not api_key:
        log("WARNING: No GROK_API_KEY set — using fallback summary")
        return generate_fallback_summary(prompt)
    try:
        import requests
        model = os.environ.get('GROK_MODEL', 'llama-3.3-70b-versatile')
        log(f"Calling Grok API (model: {model})...")
        headers = {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
        }
        payload = {
            "model": model,
            "messages": [
                {"role": "system", "content": "You are a senior business analyst. Write clear, data-driven executive summaries."},
                {"role": "user", "content": prompt},
            ],
            "temperature": 0.7,
            "max_tokens": 1000,
        }
        response = requests.post(
            "https://api.groq.com/openai/v1/chat/completions",
            headers=headers,
            json=payload,
            timeout=30,
        )
        response.raise_for_status()
        data = response.json()
        summary = data.get("choices", [{}])[0].get("message", {}).get("content", "").strip()
        log(f"LLM response received ({len(summary)} chars)")
        return summary
    except Exception as e:
        log(f"WARNING: Grok API call failed ({e}) — using fallback summary")
        return generate_fallback_summary(prompt)
