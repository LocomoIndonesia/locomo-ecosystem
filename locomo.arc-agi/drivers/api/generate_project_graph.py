import os
import json
import sys

def generate_graph(project_dir, output_path):
    print(f"Scanning directory: {project_dir}")
    nodes = []
    
    exclude_dirs = {'vendor', 'node_modules', 'storage', 'bootstrap', '.git', '.antigravity', 'tests', '__pycache__'}
    exclude_files = {'.env', '.env.example', 'composer.lock', 'package-lock.json'}

    for root, dirs, files in os.walk(project_dir):
        # Filter directories in place
        dirs[:] = [d for d in dirs if d not in exclude_dirs]
        
        for file in files:
            if file in exclude_files or file.startswith('.'):
                continue
                
            full_path = os.path.join(root, file)
            rel_path = os.path.relpath(full_path, project_dir)
            
            node_id = rel_path.replace('/', '_').replace('.', '_').lower()
            
            file_ext = os.path.splitext(file)[1]
            file_type = 'code' if file_ext in ['.php', '.js', '.py'] else 'config' if file_ext in ['.json', '.yml', '.yaml', '.xml', '.env'] else 'doc'
            
            node = {
                'label': file,
                'file_type': file_type,
                'source_file': rel_path,
                'source_location': 'L1',
                'id': node_id
            }
            nodes.append(node)
            
    graph_data = {
        "directed": True,
        "multigraph": False,
        "graph": {},
        "nodes": nodes,
        "links": [],
        "hyperedges": []
    }
    
    # Ensure parent directory exists
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    
    with open(output_path, 'w') as f:
        json.dump(graph_data, f, indent=2)
        
    print(f"Graph generated with {len(nodes)} nodes at: {output_path}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python generate_project_graph.py <project_dir> <output_path>")
        sys.exit(1)
    generate_graph(sys.argv[1], sys.argv[2])
