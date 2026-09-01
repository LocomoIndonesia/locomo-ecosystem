import os
import requests
import psycopg2
from psycopg2.extras import Json
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from pgvector.psycopg2 import register_vector

app = FastAPI(title="Locomo Cognitive Driver")

DB_URL = os.getenv("DATABASE_URL", "postgresql://postgres:postgres@pgvector-db:5432/locomo_memory")
OLLAMA_URL = os.getenv("OLLAMA_URL", "http://ollama-local:11434")
EMBEDDING_MODEL = "qwen2.5:1.5b" # Menggunakan Qwen untuk embedding & inference

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
    cur.execute("""
        CREATE TABLE IF NOT EXISTS cognitive_memories (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            project_code VARCHAR(50) REFERENCES os_projects(project_code) ON DELETE CASCADE,
            memory_type VARCHAR(50) NOT NULL,
            content TEXT NOT NULL,
            metadata JSONB,
            embedding VECTOR(1536),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    """)
    cur.execute("CREATE INDEX IF NOT EXISTS cognitive_memories_hnsw_idx ON cognitive_memories USING hnsw (embedding vector_cosine_ops);")
    conn.commit()
    cur.close()
    conn.close()

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
    embedding = res.json()["embedding"]
    
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
    embedding = res.json()["embedding"]
    
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