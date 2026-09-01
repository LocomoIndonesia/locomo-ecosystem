import argparse
import json
import urllib.request
import urllib.error
import os

def http_post(url, data):
    req = urllib.request.Request(url, method='POST')
    req.add_header('Content-Type', 'application/json')
    try:
        payload = json.dumps(data).encode('utf-8')
        with urllib.request.urlopen(req, data=payload) as response:
            res_body = response.read().decode('utf-8')
            return 200, res_body
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8')
    except Exception as e:
        return 500, str(e)

def extract_code_block(file_path, line_str, max_lines=40):
    if not os.path.exists(file_path):
        return ""
    try:
        # line_str is e.g. "L12"
        line_no = int(line_str.lstrip('L'))
    except Exception:
        return ""
        
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            lines = f.readlines()
        idx = line_no - 1
        if idx < 0 or idx >= len(lines):
            return ""
        block_lines = lines[idx : idx + max_lines]
        return "".join(block_lines)
    except Exception:
        return ""

def sync(project_code, graph_path, base_url):
    if not os.path.exists(graph_path):
        print(f"Error: Graph file '{graph_path}' not found.")
        return

    # 1. Initialize project in database
    init_url = f"{base_url}/project/init"
    print(f"Initializing project '{project_code}' on {init_url}...")
    try:
        status, text = http_post(init_url, {
            "project_code": project_code,
            "project_name": f"{project_code.upper()} Project"
        })
        if status == 200:
            print("Project initialized successfully.")
        else:
            print(f"Warning: Project initialization returned status {status}: {text}")
    except Exception as e:
        print(f"Warning: Could not initialize project: {e}")

    # 2. Parse and load graph
    with open(graph_path, 'r') as f:
        data = json.load(f)

    nodes = data.get("nodes", [])
    print(f"Found {len(nodes)} nodes. Starting synchronization for project '{project_code}'...")

    store_url = f"{base_url}/memory/store"
    synced_count = 0
    
    # Base directory for reading actual files (checking if running in container /workspace or local)
    base_dir = "/workspace" if os.path.exists("/workspace") else "."

    for i, node in enumerate(nodes):
        node_id = node.get("id")
        node_type = node.get("node_type", "file")
        source_file = node.get("source_file")
        source_location = node.get("source_location", "L1")
        label = node.get("label")
        
        target_file_path = os.path.join(base_dir, source_file) if source_file else None
        
        # Enrichment Content Logic
        payloads = []
        
        if node_type == "file" and target_file_path and os.path.exists(target_file_path):
            try:
                with open(target_file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    file_content = f.read()
                
                # Chunking strategy for large files
                if len(file_content) <= 3000:
                    payloads.append({
                        "project_code": project_code,
                        "type": node.get("file_type", "code"),
                        "content": f"File: {source_file}\nContent:\n{file_content}",
                        "metadata": node
                    })
                else:
                    chunk_size = 2500
                    overlap = 250
                    start = 0
                    chunk_idx = 0
                    while start < len(file_content):
                        end = min(start + chunk_size, len(file_content))
                        chunk_text = file_content[start:end]
                        
                        meta = node.copy()
                        meta["chunk_index"] = chunk_idx
                        meta["is_chunk"] = True
                        
                        payloads.append({
                            "project_code": project_code,
                            "type": node.get("file_type", "code"),
                            "content": f"File: {source_file} (Chunk {chunk_idx})\nContent:\n{chunk_text}",
                            "metadata": meta
                        })
                        chunk_idx += 1
                        if end == len(file_content):
                            break
                        start += chunk_size - overlap
            except Exception as e:
                print(f"Warning: Failed to read file content for {source_file}: {e}")
                
        elif node_type in ["class", "function", "method"] and target_file_path and os.path.exists(target_file_path):
            # Extract actual AST code block
            extracted_code = extract_code_block(target_file_path, source_location)
            if extracted_code:
                payloads.append({
                    "project_code": project_code,
                    "type": node.get("file_type", "code"),
                    "content": f"Symbol: {label}\nType: {node_type.upper()}\nFile: {source_file} ({source_location})\n\nCode:\n{extracted_code}",
                    "metadata": node
                })
                
        # Fallback if no enriched content is available
        if not payloads:
            payloads.append({
                "project_code": project_code,
                "type": node.get("file_type", "code"),
                "content": f"File: {source_file}\nLabel: {label}\nID: {node_id}\nType: {node_type}",
                "metadata": node
            })
            
        # Store all payloads for this node
        for payload in payloads:
            try:
                status, text = http_post(store_url, payload)
                if status == 200:
                    synced_count += 1
                else:
                    print(f"Failed to sync node {node_id}: {text}")
            except Exception as e:
                print(f"Error syncing node {node_id}: {e}")
                break
                
        if i > 0 and i % 50 == 0:
            print(f"Processed {i}/{len(nodes)} nodes, generated {synced_count} vector records...")

    print(f"Synchronization complete. Successfully stored {synced_count} semantic memory records.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Synchronize Graphify nodes to pgvector database with deep content RAG.")
    parser.add_argument("-p", "--project", required=True, help="Project code target (e.g. locomo-web)")
    parser.add_argument("-g", "--graph", required=True, help="Path to Graphify graph.json file")
    parser.add_argument("-a", "--api", default="http://localhost:8000", help="Base URL of the cognitive driver API")

    args = parser.parse_args()
    sync(args.project, args.graph, args.api)
