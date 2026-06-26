import sys, json
from .utils import log
from .input import read_input, validate_input
from .prompt import build_prompt
from .llm import call_llm
from .pdf import generate_pdf

def main():
    log("Starting report generation...")
    data = read_input()
    validate_input(data)
    prompt = build_prompt(data)
    log(f"Prompt length: {len(prompt)} chars")
    summary = call_llm(prompt)
    generate_pdf(data, summary, data['output_path'])
    result = {"summary": summary, "pdf_path": data['output_path']}
    print(json.dumps(result))
    log("Done!")
    sys.exit(0)

if __name__ == "__main__":
    main()
