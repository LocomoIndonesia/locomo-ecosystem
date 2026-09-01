#!/bin/bash

echo "Waiting for Ollama to be ready..."
until curl -s http://localhost:11434/api/tags > /dev/null; do
  sleep 2
done

echo "Pulling Qwen2.5:1.5b..."
curl -X POST http://localhost:11434/api/pull -d '{"name": "qwen2.5:1.5b"}' &

echo "Ollama initialization triggered in background."
