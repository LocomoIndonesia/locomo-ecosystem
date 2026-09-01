#!/usr/bin/env python3
import os
import sys
import json
import argparse
import datetime

WORKSPACE_DIR = "/workspace"

# Sensitive paths that cannot be modified directly
BLOCKED_PATTERNS = [
    ".git/",
    ".antigravity/id.json",
    ".antigravity/cortex/cortex-core.json"
]

def safe_path(path):
    normalized = os.path.normpath(path)
    
    # Handle absolute path translations
    for prefix in ["/home/dev360_03/locomo.id", "/workspace"]:
        if normalized.startswith(prefix):
            normalized = normalized[len(prefix):].lstrip("/")
            break
            
    rel = normalized.lstrip("/")
    full = os.path.abspath(os.path.join(WORKSPACE_DIR, rel))
    
    if not full.startswith(WORKSPACE_DIR):
        print(f"Error: Access denied. Path '{path}' is outside workspace.", file=sys.stderr)
        sys.exit(1)
        
    return rel, full

def check_guardrails(rel_path, command):
    normalized_rel = rel_path.replace("\\", "/").lstrip("/")
    
    for pattern in BLOCKED_PATTERNS:
        clean_pattern = pattern.lstrip("/")
        if normalized_rel.startswith(clean_pattern) or normalized_rel == clean_pattern:
            # Allow appending decisions.jsonl for decisions logging
            if normalized_rel == ".antigravity/cortex/decisions.jsonl" and command == "log_decision":
                continue
            print(f"Error: Security Block. Modification to sensitive path '{rel_path}' is blocked by sandbox guardrails.", file=sys.stderr)
            sys.exit(1)

def cmd_write(args):
    rel, full = safe_path(args.path)
    check_guardrails(rel, "write")
    
    try:
        os.makedirs(os.path.dirname(full), exist_ok=True)
        with open(full, 'w', encoding='utf-8') as f:
            f.write(args.content)
        print(f"Success: File '{rel}' successfully written via Docker.")
    except Exception as e:
        print(f"Error writing file: {e}", file=sys.stderr)
        sys.exit(1)

def cmd_edit(args):
    rel, full = safe_path(args.path)
    check_guardrails(rel, "edit")
    
    try:
        if not os.path.exists(full):
            print(f"Error: File '{rel}' does not exist.", file=sys.stderr)
            sys.exit(1)
            
        with open(full, 'r', encoding='utf-8') as f:
            content = f.read()
            
        if args.target not in content:
            print(f"Error: Target text not found in file '{rel}'.", file=sys.stderr)
            sys.exit(1)
            
        new_content = content.replace(args.target, args.replacement, 1)
        with open(full, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Success: File '{rel}' successfully edited via Docker.")
    except Exception as e:
        print(f"Error editing file: {e}", file=sys.stderr)
        sys.exit(1)

def cmd_log_decision(args):
    rel, full = safe_path(".antigravity/cortex/decisions.jsonl")
    check_guardrails(rel, "log_decision")
    
    log_entry = {
        "timestamp": datetime.datetime.now().isoformat(),
        "task_id": args.task_id,
        "phase": args.phase,
        "hypothesis": args.hypothesis,
        "result": args.result
    }
    
    try:
        os.makedirs(os.path.dirname(full), exist_ok=True)
        with open(full, 'a', encoding='utf-8') as f:
            f.write(json.dumps(log_entry) + "\n")
        print("Success: Decision logged successfully via Docker.")
    except Exception as e:
        print(f"Error logging decision: {e}", file=sys.stderr)
        sys.exit(1)

def cmd_changelog(args):
    try:
        mods = json.loads(args.modifications)
    except Exception as e:
        print(f"Error parsing modifications JSON: {e}", file=sys.stderr)
        sys.exit(1)
        
    if not mods:
        print("Success: No modifications to log.")
        return

    timestamp = datetime.datetime.now().strftime("%Y-%m-%d")
    filename = f"{timestamp}-local-think.json"
    rel, full = safe_path(os.path.join(".antigravity/docs/changelogs", filename))
    check_guardrails(rel, "changelog")
    
    content_md = f"### Laporan Hasil Penalaran Luring (Local Think)\n\n**Prompt Awal:** {args.prompt}\n\n**Berkas Modifikasi:**\n"
    for mod in mods:
        content_md += f"- `{mod['file']}`: {mod['action']} pada {mod['timestamp']}\n"
        
    data = {
        "timestamp": datetime.datetime.now().isoformat(),
        "title": f"Local Think: {args.prompt[:40]}...",
        "content": content_md
    }
    
    try:
        os.makedirs(os.path.dirname(full), exist_ok=True)
        with open(full, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2)
        print(f"Success: Changelog automatically written via Docker to '{rel}'.")
    except Exception as e:
        print(f"Error writing changelog: {e}", file=sys.stderr)
        sys.exit(1)

def main():
    parser = argparse.ArgumentParser(description="Docker Agent Editor Helper for Locomo Agent OS")
    subparsers = parser.add_subparsers(dest="command", required=True, help="Subcommand to execute")
    
    # Write Command
    p_write = subparsers.add_parser("write", help="Write a file to workspace")
    p_write.add_argument("--path", required=True, help="Relative or absolute path of file")
    p_write.add_argument("--content", required=True, help="Content to write")
    
    # Edit Command
    p_edit = subparsers.add_parser("edit", help="Edit file target content")
    p_edit.add_argument("--path", required=True, help="Relative or absolute path of file")
    p_edit.add_argument("--target", required=True, help="Exact target content to replace")
    p_edit.add_argument("--replacement", required=True, help="Replacement content")
    
    # Log Decision Command
    p_log = subparsers.add_parser("log_decision", help="Log agent decision")
    p_log.add_argument("--task_id", required=True, help="Task identifier")
    p_log.add_argument("--phase", required=True, help="Current phase")
    p_log.add_argument("--hypothesis", required=True, help="Details of action/hypothesis")
    p_log.add_argument("--result", required=True, help="Result outcome")
    
    # Changelog Command
    p_change = subparsers.add_parser("changelog", help="Write changelog")
    p_change.add_argument("--prompt", required=True, help="Original prompt instruction")
    p_change.add_argument("--modifications", required=True, help="JSON list of modifications")
    
    args = parser.parse_args()
    
    commands = {
        "write": cmd_write,
        "edit": cmd_edit,
        "log_decision": cmd_log_decision,
        "changelog": cmd_changelog
    }
    
    commands[args.command](args)

if __name__ == "__main__":
    main()
