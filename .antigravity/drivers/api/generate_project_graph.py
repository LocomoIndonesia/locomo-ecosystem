import os
import json
import sys
import re
import ast

def parse_python_file(content, rel_path, file_node_id):
    nodes = []
    links = []
    try:
        tree = ast.parse(content)
        # Scan for classes and their methods
        for node in ast.walk(tree):
            if isinstance(node, ast.ClassDef):
                class_id = f"{file_node_id}_class_{node.name.lower()}"
                nodes.append({
                    'label': f"Class: {node.name}",
                    'file_type': 'code',
                    'node_type': 'class',
                    'source_file': rel_path,
                    'source_location': f"L{node.lineno}",
                    'id': class_id
                })
                links.append({
                    'source': file_node_id,
                    'target': class_id,
                    'type': 'defines'
                })
                
                # Scan methods in class
                for item in node.body:
                    if isinstance(item, ast.FunctionDef):
                        method_id = f"{class_id}_method_{item.name.lower()}"
                        nodes.append({
                            'label': f"Method: {node.name}::{item.name}",
                            'file_type': 'code',
                            'node_type': 'method',
                            'source_file': rel_path,
                            'source_location': f"L{item.lineno}",
                            'id': method_id
                        })
                        links.append({
                            'source': class_id,
                            'target': method_id,
                            'type': 'defines'
                        })
        
        # Scan for top-level functions (not inside classes)
        for item in tree.body:
            if isinstance(item, ast.FunctionDef):
                func_id = f"{file_node_id}_func_{item.name.lower()}"
                nodes.append({
                    'label': f"Function: {item.name}",
                    'file_type': 'code',
                    'node_type': 'function',
                    'source_file': rel_path,
                    'source_location': f"L{item.lineno}",
                    'id': func_id
                })
                links.append({
                    'source': file_node_id,
                    'target': func_id,
                    'type': 'defines'
                })
    except Exception:
        pass
    return nodes, links

def parse_php_file(content, rel_path, file_node_id):
    nodes = []
    links = []
    class_pattern = re.compile(r'(?:class|interface|trait)\s+(\w+)', re.IGNORECASE)
    method_pattern = re.compile(r'(?:public|protected|private)?\s+function\s+(\w+)\s*\(', re.IGNORECASE)
    
    lines = content.splitlines()
    current_class_id = None
    
    for i, line in enumerate(lines, 1):
        class_match = class_pattern.search(line)
        if class_match:
            class_name = class_match.group(1)
            current_class_id = f"{file_node_id}_class_{class_name.lower()}"
            nodes.append({
                'label': f"Class: {class_name}",
                'file_type': 'code',
                'node_type': 'class',
                'source_file': rel_path,
                'source_location': f"L{i}",
                'id': current_class_id
            })
            links.append({
                'source': file_node_id,
                'target': current_class_id,
                'type': 'defines'
            })
            continue
            
        method_match = method_pattern.search(line)
        if method_match:
            method_name = method_match.group(1)
            if current_class_id:
                method_id = f"{current_class_id}_method_{method_name.lower()}"
                label = f"Method: {method_name}"
                parent = current_class_id
                node_type = 'method'
            else:
                method_id = f"{file_node_id}_func_{method_name.lower()}"
                label = f"Function: {method_name}"
                parent = file_node_id
                node_type = 'function'
                
            nodes.append({
                'label': label,
                'file_type': 'code',
                'node_type': node_type,
                'source_file': rel_path,
                'source_location': f"L{i}",
                'id': method_id
            })
            links.append({
                'source': parent,
                'target': method_id,
                'type': 'defines'
            })
    return nodes, links

def parse_js_file(content, rel_path, file_node_id):
    nodes = []
    links = []
    class_pattern = re.compile(r'class\s+(\w+)', re.IGNORECASE)
    func_pattern = re.compile(r'(?:function\s+(\w+)|(\w+)\s*=\s*(?:\([^)]*\)|[^=)]+)\s*=>|(\w+)\s*\([^)]*\)\s*\{)', re.IGNORECASE)
    
    lines = content.splitlines()
    current_class_id = None
    
    for i, line in enumerate(lines, 1):
        class_match = class_pattern.search(line)
        if class_match:
            class_name = class_match.group(1)
            current_class_id = f"{file_node_id}_class_{class_name.lower()}"
            nodes.append({
                'label': f"Class: {class_name}",
                'file_type': 'code',
                'node_type': 'class',
                'source_file': rel_path,
                'source_location': f"L{i}",
                'id': current_class_id
            })
            links.append({
                'source': file_node_id,
                'target': current_class_id,
                'type': 'defines'
            })
            continue
            
        func_match = func_pattern.search(line)
        if func_match:
            func_name = next((g for g in func_match.groups() if g), None)
            if func_name and func_name not in ('if', 'for', 'while', 'switch', 'catch', 'function'):
                if current_class_id:
                    func_id = f"{current_class_id}_method_{func_name.lower()}"
                    label = f"Method: {func_name}"
                    parent = current_class_id
                    node_type = 'method'
                else:
                    func_id = f"{file_node_id}_func_{func_name.lower()}"
                    label = f"Function: {func_name}"
                    parent = file_node_id
                    node_type = 'function'
                    
                nodes.append({
                    'label': label,
                    'file_type': 'code',
                    'node_type': node_type,
                    'source_file': rel_path,
                    'source_location': f"L{i}",
                    'id': func_id
                })
                links.append({
                    'source': parent,
                    'target': func_id,
                    'type': 'defines'
                })
    return nodes, links

def generate_graph(project_dir, output_path):
    print(f"Scanning directory: {project_dir}")
    nodes = []
    links = []
    
    exclude_dirs = {'vendor', 'node_modules', 'storage', 'bootstrap', '.git', '.antigravity', 'tests', '__pycache__'}
    exclude_files = {'.env', '.env.example', 'composer.lock', 'package-lock.json'}

    for root, dirs, files in os.walk(project_dir):
        dirs[:] = [d for d in dirs if d not in exclude_dirs]
        
        for file in files:
            if file in exclude_files or file.startswith('.'):
                continue
                
            full_path = os.path.join(root, file)
            rel_path = os.path.relpath(full_path, project_dir)
            
            file_node_id = rel_path.replace('/', '_').replace('.', '_').lower()
            file_ext = os.path.splitext(file)[1].lower()
            
            # File Type Classification
            if file_ext in ['.php', '.js', '.py']:
                file_type = 'code'
            elif file_ext in ['.json', '.yml', '.yaml', '.xml', '.env']:
                file_type = 'config'
            else:
                file_type = 'doc'
                
            # Create primary File Node
            file_node = {
                'label': file,
                'file_type': file_type,
                'node_type': 'file',
                'source_file': rel_path,
                'source_location': 'L1',
                'id': file_node_id
            }
            nodes.append(file_node)
            
            # Parse Semantic Code Nodes
            if file_type == 'code':
                try:
                    with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                    
                    if file_ext == '.py':
                        f_nodes, f_links = parse_python_file(content, rel_path, file_node_id)
                    elif file_ext == '.php':
                        f_nodes, f_links = parse_php_file(content, rel_path, file_node_id)
                    elif file_ext == '.js':
                        f_nodes, f_links = parse_js_file(content, rel_path, file_node_id)
                    else:
                        f_nodes, f_links = [], []
                        
                    nodes.extend(f_nodes)
                    links.extend(f_links)
                except Exception as e:
                    print(f"Warning: Failed to parse AST for '{rel_path}': {e}")
            
    graph_data = {
        "directed": True,
        "multigraph": False,
        "graph": {},
        "nodes": nodes,
        "links": links,
        "hyperedges": []
    }
    
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    with open(output_path, 'w') as f:
        json.dump(graph_data, f, indent=2)
        
    print(f"Graph generated with {len(nodes)} nodes and {len(links)} semantic links at: {output_path}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python generate_project_graph.py <project_dir> <output_path>")
        sys.exit(1)
    generate_graph(sys.argv[1], sys.argv[2])
