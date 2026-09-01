"""
Locomo ARC-AGI — Task Loader with Dynamic Token Routing
========================================================
Memuat soal ARC-AGI dari file JSON dan menerapkan Dynamic Token Routing:
- Grid <= 10x10: raw array dikirim utuh.
- Grid > 10x10: metadata semantik diekstrak (objek, posisi, ukuran, warna).
"""

import json
import sys
from collections import defaultdict


def load_arc_task(filepath):
    """Memuat file JSON soal ARC-AGI."""
    with open(filepath, 'r') as f:
        return json.load(f)


def extract_objects(grid):
    """Mendeteksi objek non-background (warna != 0) dari grid secara manual."""
    objects = defaultdict(list)
    for r, row in enumerate(grid):
        for c, val in enumerate(row):
            if val != 0:
                objects[val].append((r, c))
    return objects


def compute_bounding_box(coords):
    """Menghitung bounding box dari sekumpulan koordinat."""
    rows = [c[0] for c in coords]
    cols = [c[1] for c in coords]
    min_r, max_r = min(rows), max(rows)
    min_c, max_c = min(cols), max(cols)
    return {
        "position": (min_r, min_c),
        "size": (max_r - min_r + 1, max_c - min_c + 1),
        "pixel_count": len(coords)
    }


COLOR_NAMES = {
    0: "black (background)",
    1: "blue",
    2: "red",
    3: "green",
    4: "yellow",
    5: "grey",
    6: "magenta",
    7: "orange",
    8: "cyan",
    9: "brown"
}


def grid_to_semantic(grid):
    """Mengonversi grid > 10x10 menjadi deskripsi metadata semantik."""
    rows = len(grid)
    cols = len(grid[0]) if rows > 0 else 0
    objects = extract_objects(grid)

    description_parts = [f"Grid {rows}x{cols}."]

    # Background analysis
    total_cells = rows * cols
    bg_count = sum(1 for row in grid for val in row if val == 0)
    if bg_count > total_cells * 0.5:
        description_parts.append(f"Background didominasi {COLOR_NAMES[0]}.")

    # Object descriptions
    for color_val, coords in sorted(objects.items()):
        bbox = compute_bounding_box(coords)
        color_name = COLOR_NAMES.get(color_val, f"warna-{color_val}")
        description_parts.append(
            f"Objek {color_name} ({color_val}) "
            f"ukuran {bbox['size'][0]}x{bbox['size'][1]} "
            f"({bbox['pixel_count']} piksel) "
            f"di koordinat {bbox['position']}."
        )

    return " ".join(description_parts)


def format_grid_for_llm(grid):
    """
    Dynamic Token Routing:
    - Grid <= 10x10: raw array.
    - Grid > 10x10: metadata semantik.
    """
    rows = len(grid)
    cols = len(grid[0]) if rows > 0 else 0

    if rows <= 10 and cols <= 10:
        return {"format": "raw_array", "data": grid}
    else:
        return {"format": "semantic_metadata", "data": grid_to_semantic(grid)}


def process_arc_task(filepath):
    """Memproses seluruh soal ARC dan menghasilkan representasi siap-LLM."""
    task = load_arc_task(filepath)
    result = {"task_file": filepath, "train": [], "test": []}

    # Process training examples (demonstrations)
    for i, example in enumerate(task.get("train", [])):
        result["train"].append({
            "example_index": i + 1,
            "input": format_grid_for_llm(example["input"]),
            "output": format_grid_for_llm(example["output"])
        })

    # Process test examples
    for i, example in enumerate(task.get("test", [])):
        test_entry = {
            "example_index": i + 1,
            "input": format_grid_for_llm(example["input"])
        }
        if "output" in example:
            test_entry["output"] = format_grid_for_llm(example["output"])
        result["test"].append(test_entry)

    return result


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python task_loader.py <path_to_arc_task.json>")
        sys.exit(1)

    filepath = sys.argv[1]
    result = process_arc_task(filepath)
    print(json.dumps(result, indent=2, ensure_ascii=False))
