import os
import requests
import psycopg2
from psycopg2.extras import Json
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from pgvector.psycopg2 import register_vector

app = FastAPI(title="Locomo Cognitive Driver")

DB_URL = os.getenv("DATABASE_URL")
if not DB_URL:
    raise RuntimeError("DATABASE_URL belum diset — salin drivers/.env.example menjadi drivers/.env dan isi nilainya.")
OLLAMA_URL = os.getenv("OLLAMA_URL", "http://ollama-local:11434")
EMBEDDING_MODEL = "qwen2.5:1.5b" # Menggunakan Qwen untuk embedding & inference
# Standar dimensi vektor Locomo Agent OS (Tiket 2.1): 1536 = dimensi native
# qwen2.5:1.5b. DIKUNCI — ganti model berarti re-embed penuh + alter kolom.
EMBEDDING_DIM = 1536

def validate_embedding_dim(embedding, source):
    if embedding is None or len(embedding) != EMBEDDING_DIM:
        got = len(embedding) if embedding is not None else 0
        raise HTTPException(
            status_code=500,
            detail=f"Dimensi embedding {got} dari {source} tidak cocok dengan standar {EMBEDDING_DIM} "
                   f"(model {EMBEDDING_MODEL}). Periksa model Ollama — ganti model butuh migrasi (Tiket 2.1)."
        )
    return embedding

def get_db_connection(register=True):
    conn = psycopg2.connect(DB_URL)
    if register:
        cur = conn.cursor()
        cur.execute("CREATE EXTENSION IF NOT EXISTS vector;")
        conn.commit()
        cur.close()
        register_vector(conn)
    return conn

@app.on_event("startup")
def startup_event():
    # Setup Database & Tables
    conn = get_db_connection(register=True)
    cur = conn.cursor()
    cur.execute("""
        CREATE TABLE IF NOT EXISTS os_projects (
            project_code VARCHAR(50) PRIMARY KEY,
            project_name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    """)
    cur.execute(f"""
        CREATE TABLE IF NOT EXISTS cognitive_memories (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            project_code VARCHAR(50) REFERENCES os_projects(project_code) ON DELETE CASCADE,
            memory_type VARCHAR(50) NOT NULL,
            content TEXT NOT NULL,
            metadata JSONB,
            embedding VECTOR({EMBEDDING_DIM}),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    """)
    # Fail-fast bila kolom lama berdimensi lain (mis. sisa migrasi parsial)
    cur.execute("""
        SELECT atttypmod FROM pg_attribute
        JOIN pg_class ON pg_class.oid = pg_attribute.attrelid
        WHERE pg_class.relname = 'cognitive_memories' AND pg_attribute.attname = 'embedding';
    """)
    row = cur.fetchone()
    # pgvector menyimpan typmod = dimensi langsung (terbukti live: VECTOR(1536) -> atttypmod 1536).
    if row and row[0] > 0 and row[0] != EMBEDDING_DIM:
        raise RuntimeError(
            f"Kolom cognitive_memories.embedding berdimensi {row[0]}, standar {EMBEDDING_DIM}. "
            "Jalankan migrasi dimensi sebelum start (Tiket 2.1)."
        )
    cur.execute("CREATE INDEX IF NOT EXISTS cognitive_memories_hnsw_idx ON cognitive_memories USING hnsw (embedding vector_cosine_ops);")
    conn.commit()
    cur.close()
    conn.close()
    # Probe dimensi model Ollama: mismatch = hard fail dengan pesan jelas;
    # Ollama mati = warning degrade (bukan crash) agar API tetap bisa start.
    try:
        probe = requests.post(f"{OLLAMA_URL}/api/embeddings",
                              json={"model": EMBEDDING_MODEL, "prompt": "locomo dimension probe"},
                              timeout=15)
        if probe.status_code == 200:
            validate_embedding_dim(probe.json()["embedding"], f"model {EMBEDDING_MODEL}")
            print(f"[STARTUP] Dimensi embedding OK: {EMBEDDING_DIM} ({EMBEDDING_MODEL}).")
        else:
            print(f"[STARTUP] Warning: probe embedding status {probe.status_code} — validasi dimensi dilewati.")
    except HTTPException as e:
        raise RuntimeError(e.detail)
    except Exception as e:
        print(f"[STARTUP] Warning: Ollama tidak terjangkau ({e}) — validasi dimensi dilewati, mode degrade.")

class ProjectInit(BaseModel):
    project_code: str
    project_name: str

@app.post("/project/init")
def init_project(data: ProjectInit):
    conn = get_db_connection()
    cur = conn.cursor()
    cur.execute(
        "INSERT INTO os_projects (project_code, project_name) VALUES (%s, %s) ON CONFLICT (project_code) DO NOTHING",
        (data.project_code, data.project_name)
    )
    conn.commit()
    return {"status": "success", "project": data.project_code}

class MemoryStore(BaseModel):
    project_code: str
    type: str
    content: str
    metadata: dict

@app.post("/memory/store")
def store_memory(data: MemoryStore):
    # Generate Embedding via Ollama
    res = requests.post(f"{OLLAMA_URL}/api/embeddings", json={"model": EMBEDDING_MODEL, "prompt": data.content})
    if res.status_code != 200:
        raise HTTPException(status_code=500, detail="Ollama embedding failed")
    embedding = validate_embedding_dim(res.json()["embedding"], f"model {EMBEDDING_MODEL}")

    conn = get_db_connection()
    cur = conn.cursor()

    node_id = data.metadata.get("id")
    if node_id:
        cur.execute(
            "SELECT id FROM cognitive_memories WHERE project_code = %s AND metadata->>'id' = %s",
            (data.project_code, node_id)
        )
        existing = cur.fetchone()
        if existing:
            cur.execute(
                "UPDATE cognitive_memories SET memory_type = %s, content = %s, metadata = %s, embedding = %s WHERE id = %s",
                (data.type, data.content, Json(data.metadata), embedding, existing[0])
            )
            conn.commit()
            cur.close()
            conn.close()
            return {"status": "updated"}
            
    cur.execute(
        "INSERT INTO cognitive_memories (project_code, memory_type, content, metadata, embedding) VALUES (%s, %s, %s, %s, %s)",
        (data.project_code, data.type, data.content, Json(data.metadata), embedding)
    )
    conn.commit()
    cur.close()
    conn.close()
    return {"status": "stored"}

class MemoryQuery(BaseModel):
    project_code: str
    query: str
    top_k: int = 3

@app.post("/memory/query")
def query_memory(data: MemoryQuery):
    res = requests.post(f"{OLLAMA_URL}/api/embeddings", json={"model": EMBEDDING_MODEL, "prompt": data.query})
    if res.status_code != 200:
        raise HTTPException(status_code=500, detail="Ollama embedding failed")
    embedding = validate_embedding_dim(res.json()["embedding"], f"model {EMBEDDING_MODEL}")
    
    conn = get_db_connection()
    cur = conn.cursor()
    # Cosine distance (<=>) search
    cur.execute("""
        SELECT content, metadata, 1 - (embedding <=> %s::vector) AS similarity 
        FROM cognitive_memories 
        WHERE project_code = %s 
        ORDER BY embedding <=> %s::vector LIMIT %s
    """, (embedding, data.project_code, embedding, data.top_k))
    
    results = [{"content": row[0], "metadata": row[1], "score": row[2]} for row in cur.fetchall()]
    return {"status": "success", "data": results}

class MemoryPrune(BaseModel):
    project_code: str
    active_ids: List[str]

@app.post("/memory/prune")
def prune_memory(data: MemoryPrune):
    # Tiket 2.2 (reconcile): hapus baris project yang metadata id-nya
    # tidak lagi ada di graf. Baris tanpa id (legacy) dipertahankan.
    conn = get_db_connection()
    cur = conn.cursor()
    cur.execute(
        "DELETE FROM cognitive_memories WHERE project_code = %s "
        "AND metadata->>'id' IS NOT NULL AND NOT (metadata->>'id' = ANY(%s))",
        (data.project_code, data.active_ids)
    )
    deleted = cur.rowcount
    conn.commit()
    cur.close()
    conn.close()
    return {"status": "success", "pruned": deleted}

class LocalInference(BaseModel):
    system_prompt: str
    prompt: str

@app.post("/agent/local-inference")
def local_inference(data: LocalInference):
    res = requests.post(f"{OLLAMA_URL}/api/generate", json={
        "model": EMBEDDING_MODEL,
        "system": data.system_prompt,
        "prompt": data.prompt,
        "stream": False
    })
    return {"status": "success", "response": res.json()["response"]}